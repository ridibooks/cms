<?php

use Moriony\Silex\Provider\SentryServiceProvider;

$config = [
    'debug' => $_ENV['DEBUG'],
    'test_id' => $_ENV['TEST_ID'],
    'azure.options' => [
        'tenent' => $_ENV['AZURE_TENENT'] ?? '',
        'client_id' => $_ENV['AZURE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['AZURE_CLIENT_SECRET'] ?? '',
        'resource' => $_ENV['AZURE_RESOURCE'] ?? '',
        'redirect_uri' => $_ENV['AZURE_REDIRECT_URI'] ?? '',
        'api_version' => $_ENV['AZURE_API_VERSION'] ?? '',
    ],
    'capsule.connections' => [
        'default' => [
            'driver' => 'mysql',
            'host' => $_ENV['MYSQL_HOST'] ?? 'localhost',
            'database' => $_ENV['MYSQL_DATABASE'] ?? 'cms',
            'username' => $_ENV['MYSQL_USER'] ?? 'root',
            'password' => $_ENV['MYSQL_PASSWORD'] ?? '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]
    ],
    'capsule.options' => [
        'setAsGlobal' => true,
        'bootEloquent' => true,
        'enableQueryLog' => false,
    ],
    SentryServiceProvider::SENTRY_OPTIONS => [
        SentryServiceProvider::OPT_DSN => $_ENV['SENTRY_KEY'] ?? ''
    ],
    'twig.globals' => [
        'STATIC_URL' => '/static',
        'BOWER_PATH' => '/static/bower_components',
    ],
    'twig.path' => [
        __DIR__ . '/../views/'
    ],
];

return $config;
