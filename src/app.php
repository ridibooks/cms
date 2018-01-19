<?php
declare(strict_types=1);

use JG\Silex\Provider\CapsuleServiceProvider;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Service\LoginService;

$app = new CmsApplication();
$app->register(new CapsuleServiceProvider());
$app->register(new SentryServiceProvider());

return $app;
