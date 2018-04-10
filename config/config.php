<?php

use Moriony\Silex\Provider\SentryServiceProvider;

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
    'auth.enabled' => ['oauth2', 'password', 'test'],
    'auth.options' => [

        // oauth2 authenticator
        'oauth2' => [
            'authorize' => '/auth/oauth2/{provider}/authorize',
            'callback' => '/auth/oauth2/callback',
        ],

        // password authenticator
        'password' => [
            'login' => '/auth/password/authorize',
        ],

        // test authenticator
        'test' => [
            'login' => '/auth/test/authorize',
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
