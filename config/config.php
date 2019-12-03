<?php

use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\CFAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;
use Symfony\Component\HttpFoundation\Request;

$auth_enabled = [
    OAuth2Authenticator::AUTH_TYPE,
    CFAuthenticator::AUTH_TYPE,
];

if (!empty($_ENV['AUTH_USE_TEST'])) {
    $auth_enabled[] = TestAuthenticator::AUTH_TYPE;
}


$request = Request::createFromGlobals();
// trust *all* requests
Request::setTrustedProxies(
    array('127.0.0.1', $request->server->get('REMOTE_ADDR')),
    Request::HEADER_X_FORWARDED_ALL
);

// If hostname has a form of dev domain, set test id.
if (!empty($_ENV['TEST_AUTH_DISABLE'])) {
    $patterns = [
        '/^admin\.(\w+)(\.platform)?\.dev\.ridi\.io$/', // 'admin.{test_id}.dev.io', 'admin.{test_id}.platform.dev.io'
        '/^cms\.(\w+)(\.platform)?\.dev\.ridi\.io$/', // 'cms.{test_id}.dev.io' or 'cms.{test_id}.platform.dev.io'
        '/^admin\.(\w+)\.test\.ridi\.io$/', // 'admin.{test_id}.test.ridi.io'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $request->getHost(), $matches)) {
            $user_id_for_test_domain = $matches[1];
            break;
        }
    }
}

// Create a dynamic redirect uri based on request domain.
if (!empty($_ENV['AZURE_REDIRECT_PATH'])) {
    if (empty(parse_url($_ENV['AZURE_REDIRECT_PATH'], PHP_URL_SCHEME))) {
        $_ENV['AZURE_REDIRECT_URI'] = $request->getSchemeAndHttpHost() . $_ENV['AZURE_REDIRECT_PATH'];
    } else {
        $_ENV['AZURE_REDIRECT_URI'] = $_ENV['AZURE_REDIRECT_PATH'];
    }
}

$config = [
    'debug' => $_ENV['DEBUG'] ?? false,
    'oauth2.options' => [
        AzureClient::PROVIDER_NAME => [
            'tenent' => $_ENV['AZURE_TENENT'] ?? '',
            'clientId' => $_ENV['AZURE_CLIENT_ID'] ?? '',
            'clientSecret' => $_ENV['AZURE_CLIENT_SECRET'] ?? '',
            'redirectUri' => $_ENV['AZURE_REDIRECT_URI'] ?? '',
            'resource' => $_ENV['AZURE_RESOURCE'] ?? '',
        ],
    ],

    //TODO: Remove this after OAuth2 authorization is implemented
    'auth.is_secure' => $request->isSecure(),

    'auth.enabled' => $auth_enabled,
    'auth.instant_auth' => $_ENV['INSTANT_AUTH'] ?? '',
    'auth.options' => [

        // oauth2 authenticator
        'oauth2' => [
        ],

        // test authenticator
        'test' => [
            'test_user_id' => $user_id_for_test_domain ?? $_ENV['TEST_ID'],
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
    'thrift.rpc_url' => $_ENV['CMS_RPC_URL'],
    'thrift.rpc_secret' => $_ENV['CMS_RPC_SECRET'],
    'auth.test_id' =>  $_ENV['TEST_ID'],
    'auth.cf_access_domain' => $_ENV['CF_ACCESS_DOMAIN'],
    'auth.cf_audience_tag' => $_ENV['CF_AUDIENCE_TAG'],
];

return $config;
