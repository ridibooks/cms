<?php
declare(strict_types=1);

use JG\Silex\Provider\CapsuleServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Service;
use Ridibooks\Cms\Thrift;
use Silex\Provider\MonologServiceProvider;

$app = new CmsApplication($config);

$app->register(new Silex\Provider\RoutingServiceProvider());

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

$app->register(new Service\AzureServiceProvider(), [
    'azure.options' => $app['azure.options'],
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

return $app;
