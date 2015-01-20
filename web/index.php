<?php

date_default_timezone_set('UTC');

define('SYNCMEETUP_PUBLIC_ROOT', __DIR__);

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use GoogleCal\Repository\MeetupDetailsRepository;
use GoogleCal\Repository\GoogleDetailsRepository;
use GoogleCal\Repository\UserRepository;
use GoogleCal\Service\UserService;
use GoogleCal\Service\GoogleService;
use GoogleCal\Service\MeetupService;
use GoogleCal\Controller\RootController;
use GoogleCal\Controller\Controller;
use GoogleCal\Controller\AuthController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

// Configuration
require __DIR__.'/../app/config/prod.php';

// Debug Mode
$app['debug'] = true;

// Register Service Providers
$app->register(new ServiceControllerServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true,
    ),
    'twig.path' => array(__DIR__ . '/../app/views')
));
$app->register(new TranslationServiceProvider(), array(
    'translator.domains' => array(),
));

// Start Session
$app->before(function ($request) {
    $request->getSession()->start();
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

$app['service.google'] = $app->share(function ($app) {
    return new GoogleService(
        $app,
        $app['repository.google_details']);
});

$app['service.meetup'] = $app->share(function ($app) {
    return new MeetupService(
        $app,
        $app['repository.meetup_details']);
});

// Register Controllers
$app['controller.base'] = $app->share(function() use ($app) {
   return new Controller(
       $app['twig'],
       $app['session'],
       $app['url_generator'],
       $app['form.factory']);
});

$app['controller.root'] = $app->share(function() use ($app) {
    $rootController = new RootController(
        $app['service.user'],
        $app['service.meetup'],
        $app['service.google']);

    $rootController->populateParent(
        $app['twig'],
        $app['session'],
        $app['form.factory'],
        $app['url_generator']);

    return $rootController;
});

$app['controller.auth'] = $app->share(function() use ($app) {
   $authController = new AuthController(
        $app['service.google'],
        $app['service.user'],
        $app['service.meetup']);

    $authController->populateParent(
        $app['twig'],
        $app['session'],
        $app['form.factory'],
        $app['url_generator']);

    return $authController;
});

$app['controller.calendar'] = $app->share(function() use ($app) {
    $authController = new AuthController(
        $app['service.google'],
        $app['service.user'],
        $app['service.meetup']);

    $authController->populateParent(
        $app['twig'],
        $app['session'],
        $app['form.factory'],
        $app['url_generator']);

    return $authController;
});

// Register Middlewares
$checkGoogleExpiryDate = function (Request $request, $app) {
    $userService = $app['service.user'];
    $urlGenerator = $app['url_generator'];

    $user = $userService->getLoggedInUser();

    $googleDetails = $user->getGoogleDetails();
    if ($googleDetails->hasExpired()) {
        $redirectUrl = $urlGenerator->generate('auth.refresh.google',
            array('referrer' => 'google.selectCalendar'));
        return new RedirectResponse($redirectUrl);
    }
};

$checkMeetupExpiryDate  = function (Request $request, $app) {

};

// Register Routes
$app->get('/', 'controller.root:indexAction')
    ->before($checkGoogleExpiryDate)
    ->bind('home');
$app->get('/auth/connect/meetup', 'controller.auth:connectMeetupAction')
    ->bind('auth.connect.meetup');
$app->get('/auth/refresh/meetup', 'controller.auth:refreshMeetupAction')
    ->bind('auth.refresh.meetup');
$app->get('/auth/connect/google', 'controller.auth:connectGoogleAction')
    ->bind('auth.connect.google');
$app->get('/auth/refresh/google', 'controller.auth:refreshGoogleAction')
    ->bind('auth.refresh.google');
$app->get('/google/selectCalendar', 'controller.google:selectCalendarAction')
    ->bind('google.selectCalendar');
$app->post('/google/selectCalendar', 'controller.google:selectCalendarAction')
    ->bind('google.selectedCalendar');
$app->get('/google/sync', 'controller.google:syncAction')
    ->bind('google.sync');

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
