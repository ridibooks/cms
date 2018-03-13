<?php
declare(strict_types=1);

use JG\Silex\Provider\CapsuleServiceProvider;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\CmsApplication;

$app = new CmsApplication($config);
$app->register(new CapsuleServiceProvider(), [
    'capsule.connections' => $app['capsule.connections'],
    'capsule.options' => $app['capsule.options'],
]);
$app->register(new SentryServiceProvider(), [
    SentryServiceProvider::SENTRY_OPTIONS => $app[SentryServiceProvider::SENTRY_OPTIONS]
]);

return $app;
