<?php
declare(strict_types=1);

use JG\Silex\Provider\CapsuleServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Service\AdminAuthService;
use Ridibooks\Cms\Service\AdminMenuService;
use Ridibooks\Cms\Service\AdminTagService;
use Ridibooks\Cms\Service\AdminUserService;
use Ridibooks\Cms\Service\AzureServiceProvider;
use Ridibooks\Cms\Service\ThriftServiceProvider;
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

$app->register(new MonologServiceProvider(), [
    'monolog.name' => 'CMS',
    'monolog.handler' => new StreamHandler('php://stdout', Logger::INFO),
]);

$app->register(new AzureServiceProvider(), [
    'azure.options' => $app['azure.options'],
]);

$app->register(new ThriftServiceProvider(), [
    'thrift.logger' => $app['logger'],
    'thrift.services' => function () {
        return [
            'AdminAuth' => new AdminAuthService(),
            'AdminMenu' => new AdminMenuService(),
            'AdminTag' => new AdminTagService(),
            'AdminUser' => new AdminUserService(),
        ];
    },
]);

return $app;
