<?php

use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\PasswordAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;

$auth_enabled = [
    OAuth2Authenticator::AUTH_TYPE,
];

if (!empty($_ENV['AUTH_USE_TEST'])) {
    $auth_enabled[] = TestAuthenticator::AUTH_TYPE;
}

if (!empty($_ENV['AUTH_USE_PASSWORD'])) {
    $auth_enabled[] = PasswordAuthenticator::AUTH_TYPE;
}

$config = [
    'debug' => $_ENV['DEBUG'],
    'oauth2.options' => [
        AzureClient::PROVIDER_NAME => [
            'tenent' => $_ENV['AZURE_TENENT'] ?? '',
            'clientId' => $_ENV['AZURE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['AZURE_CLIENT_SECRET'] ?? '',
            'redirectUri' => $_ENV['AZURE_REDIRECT_URI'] ?? '',
            'redirectPath' => $_ENV['AZURE_REDIRECT_PATH'] ?? '',
            'resource' => $_ENV['AZURE_RESOURCE'],
        ],
    ],
    'auth.enabled' => $auth_enabled,
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
