<?php
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\Controller\AdminMenuControllerProvider;
use Ridibooks\Platform\Cms\Controller\AdminTagControllerProvider;
use Ridibooks\Platform\Cms\Controller\AdminUserControllerProvider;
use Ridibooks\Platform\Cms\MiniRouter;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../config.php';

$autoloader = require __DIR__ . "/../vendor/autoload.php";
$autoloader->addPsr4('Ridibooks\\Platform\\Cms\\', __DIR__ . '/server/src');

LoginService::startSession();


// Try Silex Route next
$app = new CmsApplication();
$app['debug'] = \Config::$UNDER_DEV;
$app['twig.path'] = [
	__DIR__ . '/server/views'
];

// Try MiniRouter first
$app->before(function (Request $request) {
	return MiniRouter::shouldRedirectForLogin($request);
});

$app->error(function (\Exception $e) use ($app) {
	if ($app['debug']) {
		return null;
	}

	throw $e;
});

$app->mount('/', new AdminUserControllerProvider());
$app->mount('/', new AdminTagControllerProvider());
$app->mount('/', new AdminMenuControllerProvider());

$app->run();
