<?php

define('SYNCMEETUP_PUBLIC_ROOT', __DIR__);

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use GoogleCal\Helper\ClientHelper;
use GoogleCal\Repository\MeetupDetailsRepository;
use GoogleCal\Repository\GoogleDetailsRepository;
use GoogleCal\Repository\UserRepository;
use GoogleCal\Service\UserService;
use GoogleCal\Controller\DefaultController;
use GoogleCal\Controller\GoogleController;
use GoogleCal\Controller\MeetupController;

// Configuration
require __DIR__.'/../app/config/prod.php';

// Debug Mode
$app['debug'] = true;

// Register Service Providers
$app->register(new ServiceControllerServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true,
    ),
    'twig.path' => array(__DIR__ . '/../app/views')
));

// Start Session
$app->before(function ($request) {
    $request->getSession()->start();
});

// Register Helpers
$app['helper.client'] = $app->share(function ($app) {
    return new ClientHelper($app);
});

// Register Repositories
$app['repository.meetup_details'] = $app->share(function ($app) {
    return new MeetupDetailsRepository($app['db']);
});

$app['repository.google_details'] = $app->share(function ($app) {
    return new GoogleDetailsRepository($app['db']);
});

$app['repository.user'] = $app->share(function ($app) {
    return new UserRepository(
        $app['db'],
        $app['repository.meetup_details'],
        $app['repository.google_details']);
});

// Register Services
$app['service.user'] = $app->share(function ($app) {
    return new UserService(
        $app['repository.user'],
        $app['repository.meetup_details'],
        $app['repository.google_details'],
        $app['session']);
});

// Register Controllers
$app['controller.default'] = $app->share(function() use ($app) {
    return new DefaultController(
        $app['service.user'],
        $app['helper.client'],
        $app['twig']);
});

$app['controller.google'] = $app->share(function() use ($app) {
    return new GoogleController(
        $app['session'],
        $app['helper.client']);
});

$app['controller.meetup'] = $app->share(function() use ($app) {
    return new MeetupController(
        $app['session'],
        $app['service.user'],
        $app['helper.client']);
});

// Register Routes
$app->get('/', 'controller.default:indexAction');
$app->get('/meetup/connect', 'controller.meetup:connectAction');
$app->get('/google/connect', 'controller.google:connectAction');

// Register the error handler.
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        echo($e);
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.' . $e;
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

$app->run();
