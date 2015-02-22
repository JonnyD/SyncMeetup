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
use GoogleCal\Repository\EventRepository;
use GoogleCal\Service\UserService;
use GoogleCal\Service\GoogleService;
use GoogleCal\Service\MeetupService;
use GoogleCal\Service\EventService;
use GoogleCal\Controller\RootController;
use GoogleCal\Controller\AuthController;
use GoogleCal\Controller\CalendarController;
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

$app['repository.event'] = $app->share(function ($app) {
    return new EventRepository($app['db']);
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

$app['service.event'] = $app->share(function ($app) {
    return new EventService(
        $app['repository.event']);
});

// Register Controllers
$app['controller.root'] = $app->share(function() use ($app) {
    return new RootController(
        $app['service.user'],
        $app['service.meetup'],
        $app['service.google'],
        $app['twig'],
        $app['session'],
        $app['form.factory'],
        $app['url_generator']);
});

$app['controller.auth'] = $app->share(function() use ($app) {
   return new AuthController(
        $app['service.google'],
        $app['service.user'],
        $app['service.meetup'],
        $app['twig'],
        $app['session'],
        $app['form.factory'],
        $app['url_generator']);
});

$app['controller.calendar'] = $app->share(function() use ($app) {
    return new CalendarController(
        $app['service.google'],
        $app['service.user'],
        $app['service.meetup'],
        $app['service.event'],
        $app['twig'],
        $app['session'],
        $app['form.factory'],
        $app['url_generator']);
});

// Register Middlewares
$isLoggedIn = function (Request $request, $app) {
    $userService = $app['service.user'];
    $urlGenerator = $app['url_generator'];

    $user = $userService->getLoggedInUser();
    if ($user == null) {
        $redirectUrl = $urlGenerator->generate('home');
        return new RedirectResponse($redirectUrl);
    }
};

$checkGoogleExpiryDate = function (Request $request, $app) {
    $userService = $app['service.user'];
    $urlGenerator = $app['url_generator'];

    $user = $userService->getLoggedInUser();

    $googleDetails = $user->getGoogleDetails();
    if ($googleDetails->hasExpired()) {
        $route = $app['routes']->get($request->get('_route'));
        $path = str_replace("/", ".", ltrim($route->getPath(), '/'));
        $redirectUrl = $urlGenerator->generate('auth.refresh.google', array('referrer' => $path));
        return new RedirectResponse($redirectUrl);
    }
};

$checkMeetupExpiryDate  = function (Request $request, $app) {
    $userService = $app['service.user'];
    $urlGenerator = $app['url_generator'];

    $user = $userService->getLoggedInUser();

    $meetupDetails = $user->getMeetupDetails();
    if ($meetupDetails->hasExpired()) {
        $route = $app['routes']->get($request->get('_route'));
        $path = str_replace("/", ".", ltrim($route->getPath(), '/'));
        $redirectUrl = $urlGenerator->generate('auth.refresh.meetup', array('referrer' => $path));
        return new RedirectResponse($redirectUrl);
    }
};

// Register Routes
$app->get('/', 'controller.root:indexAction')
    ->bind('home');
$app->get('/auth/connect/meetup', 'controller.auth:connectMeetupAction')
    ->bind('auth.connect.meetup');
$app->get('/auth/refresh/meetup', 'controller.auth:refreshMeetupAction')
    ->bind('auth.refresh.meetup');
$app->get('/auth/connect/google', 'controller.auth:connectGoogleAction')
    ->bind('auth.connect.google');
$app->get('/auth/refresh/google', 'controller.auth:refreshGoogleAction')
    ->bind('auth.refresh.google');
$app->get('/calendar/select', 'controller.calendar:selectAction')
    ->before($checkGoogleExpiryDate)
    ->bind('calendar.selectCalendar');
$app->post('/calendar/select', 'controller.calendar:selectAction')
    ->before($checkGoogleExpiryDate);
$app->post('/calendar/selected', 'controller.calendar:selectedAction')
    ->bind('calendar.selectedCalendar');
$app->get('/calendar/sync', 'controller.calendar:syncAction')
    ->before($checkGoogleExpiryDate)
    ->before($checkMeetupExpiryDate)
    ->bind('calendar.sync');

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
