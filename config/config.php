<?php

use Moriony\Silex\Provider\SentryServiceProvider;

$app['debug'] = $_ENV['DEBUG'];
$app['test_id'] = $_ENV['TEST_ID'];

$app['azure'] = [
    'tenent' => $_ENV['AZURE_TENENT'] ?? '',
    'client_id' => $_ENV['AZURE_CLIENT_ID'] ?? '',
    'client_secret' => $_ENV['AZURE_CLIENT_SECRET'] ?? '',
    'resource' => $_ENV['AZURE_RESOURCE'] ?? '',
    'redirect_uri' => $_ENV['AZURE_REDIRECT_URI'] ?? '',
    'api_version' => $_ENV['AZURE_API_VERSION'] ?? '',
];

$app['twig.path'] = __DIR__ . '/../views/';

$app['capsule.connections'] = [
    'default' => [
        'driver' => 'mysql',
        'host' => $_ENV['MYSQL_HOST'] ?? 'localhost',
        'database' => $_ENV['MYSQL_DATABASE'] ?? 'cms',
        'username' => $_ENV['MYSQL_USER'] ?? 'root',
        'password' => $_ENV['MYSQL_PASSWORD'] ?? '',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ]
];
$app['capsule.options'] = [
    'setAsGlobal' => true,
    'bootEloquent' => true,
    'enableQueryLog' => false,
];

$app[SentryServiceProvider::SENTRY_OPTIONS] = [
    SentryServiceProvider::OPT_DSN => $_ENV['SENTRY_KEY'] ?? ''
];
