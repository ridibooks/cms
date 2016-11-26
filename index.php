<?php
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\MiniRouter;
use Ridibooks\Platform\Cms\UserControllerProvider;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../config.php';

$autoloader = require __DIR__ . "/vendor/autoload.php";

LoginService::startSession();


// Try Silex Route next
$app = new CmsApplication();

// Try MiniRouter first
$app->before(function (Request $request) {
	return MiniRouter::shouldRedirectForLogin($request);
});

$app->mount('/', new UserControllerProvider());

$app->run();
