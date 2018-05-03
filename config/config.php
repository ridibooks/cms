<?php

use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\Service\Auth\AuthServiceProvider;

$config = [
    'debug' => $_ENV['DEBUG'],
    'oauth2.options' => [
        'azure' => [
            'tenent' => $_ENV['AZURE_TENENT'] ?? '',
            'clientId' => $_ENV['AZURE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['AZURE_CLIENT_SECRET'] ?? '',
            'redirectUri' => $_ENV['AZURE_REDIRECT_URI'] ?? '',
            'redirect_path' => $_ENV['AZURE_REDIRECT_PATH'] ?? '',
            'resource' => $_ENV['AZURE_RESOURCE'],
        ],
    ],
    'auth.enabled' => [
        AuthServiceProvider::AUTH_TYPE_OAUTH2,
        AuthServiceProvider::AUTH_TYPE_PASSWORD,
        AuthServiceProvider::AUTH_TYPE_TEST,
    ],
    'auth.options' => [

        // oauth2 authenticator
        'oauth2' => [
        ],

        // password authenticator
        'password' => [
        ],

        // test authenticator
        'test' => [
            'test_user_id' => $_ENV['TEST_ID'],
        ],
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
