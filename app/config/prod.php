<?php

// Timezone.
date_default_timezone_set('Europe/Dublin');

// Cache
$app['cache.path'] = __DIR__ . '/../cache';

// Twig cache
$app['twig.options.cache'] = $app['cache.path'] . '/twig';

// Emails
$app['admin_email'] = 'noreply@nothing.nothing';
$app['site_email'] = 'noreply@nothing.nothing';

// Google API Credentials
$app['google.client_id'] = 'xxxxxxxxxxxxxxxxx';
$app['google.client_secret'] = 'xxxxxxxxxxxxxxxxx';
$app['google.redirect_uri'] = 'http://xxxxxxxxxxxxxxxxx';

// Meetup API Credentials
$app['meetup.client_id'] = 'xxxxxxxxxxxxxxxxx';
$app['meetup.client_secret'] = 'xxxxxxxxxxxxxxxxx';
$app['meetup.redirect_uri'] = 'http://xxxxxxxxxxxxxxxxx';

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'dbname'   => 'dbname',
    'user'     => 'root',
    'password' => '',
);

// Doctrine (orm)
$app['orm.em.options'] = array(
    "mappings" => array(
        array(
            "type" => "annotation",
            "namespace" => "GoogleCal\Entity",
            "path" => __DIR__."/../src/GoogleCal/Entity",
        ),
    ),
);

// SwiftMailer
// See http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app['swiftmailer.options'] = array(
    'host' => 'host',
    'port' => '25',
    'username' => 'username',
    'password' => 'password',
    'encryption' => null,
    'auth_mode' => null
);