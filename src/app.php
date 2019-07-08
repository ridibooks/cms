<?php
declare(strict_types=1);

use JG\Silex\Provider\CapsuleServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Service;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;
use Ridibooks\Cms\Thrift;
use Silex\Provider\MonologServiceProvider;

$app = new CmsApplication($config);

$app->register(new Silex\Provider\RoutingServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new CapsuleServiceProvider(), [
    'capsule.connections' => $app['capsule.connections'],
    'capsule.options' => $app['capsule.options'],
]);

$app->register(new SentryServiceProvider(), [
    SentryServiceProvider::SENTRY_OPTIONS => $app[SentryServiceProvider::SENTRY_OPTIONS]
]);
$app[SentryServiceProvider::SENTRY]->install();

$app->register(new MonologServiceProvider(), [
    'monolog.name' => 'CMS',
    'monolog.handler' => new StreamHandler('php://stdout', Logger::INFO),
]);

$app->register(new Service\ThriftServiceProvider(), [
    'thrift.logger' => $app['logger'],
    'thrift.services' => function () {
        return [
            'AdminAuth' => new Thrift\AdminAuthThrift(new Service\AdminAuthService()),
            'AdminMenu' => new Thrift\AdminMenuThrift(new Service\AdminMenuService()),
            'AdminTag' => new Thrift\AdminTagThrift(new Service\AdminTagService()),
            'AdminUser' => new Thrift\AdminUserThrift(new Service\AdminUserService()),
        ];
    },
]);

$app->register(new Service\Auth\AuthenticationServiceProvider(), [
    'auth.options' => $config['auth.options'],
    'auth.oauth2.clients' => function (CmsApplication $app) {
        return [
            AzureClient::PROVIDER_NAME => new AzureClient($app['oauth2.options']['azure']),
        ];
    },
]);

$app->after(function (Request $request, Response $response) {
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Content-Security-Policy-Report-Only', "default-src 'self'; report-uri https://gyx3tts4g8.execute-api.ap-northeast-2.amazonaws.com/default/perf-csp-reports-lambda;");
});$

// TODO: error handler
//$app->error(function () {
//
//});

return $app;
