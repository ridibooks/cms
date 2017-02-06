<?php
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\MiniRouter;
use Ridibooks\Platform\Cms\UserControllerProvider;
use Symfony\Component\HttpFoundation\Request;

if (is_readable(__DIR__ . '/../config.php')) {
	require_once __DIR__ . '/../config.php';
} elseif (is_readable(__DIR__ . '/config.local.php')) {
	require_once __DIR__ . '/config.local.php';
}

$autoloader = require __DIR__ . "/vendor/autoload.php";

if (isset(\Config::$COUCHBASE_ENABLE) && \Config::$COUCHBASE_ENABLE) {
	LoginService::startCouchbaseSession(\Config::$COUCHBASE_SERVER_HOSTS);
} else {
	LoginService::startSession();
}


// Try Silex Route next
$app = new CmsApplication();

// Try MiniRouter first
$app->before(function (Request $request) {
	return MiniRouter::shouldRedirectForLogin($request, \Config::$ENABLE_SSL);
});

$app->mount('/', new UserControllerProvider());

$app->run();
