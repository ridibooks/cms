<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\Controller\SuperControllerProvider;
use Ridibooks\Platform\Cms\Controller\UserControllerProvider;
use Ridibooks\Platform\Cms\CouchbaseSessionHandler;
use Ridibooks\Platform\Cms\MiniRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../include/config.php';
require __DIR__ . "/vendor/autoload.php";

const SESSION_TIMEOUT_SEC = 60 * 60 * 24 * 30;

if (strlen(Config::$COUCHBASE_ENABLE)) {
	session_set_save_handler(new CouchbaseSessionHandler(implode(',', \Config::$COUCHBASE_SERVER_HOSTS), 'session_cms', SESSION_TIMEOUT_SEC), true);
}
session_set_cookie_params(SESSION_TIMEOUT_SEC, '/', Config::$ADMIN_DOMAIN);
session_start();

// Try MiniRouter first
$response = MiniRouter::shouldRedirectForLogin(Request::createFromGlobals());
if ($response) {
	$response->send();
	exit;
}


// Try Silex Route next
$app = new CmsApplication();
$app['debug'] = \Config::$UNDER_DEV;
$app['twig.path'] = [
	__DIR__ . '/views'
];

$app->error(function (\Exception $e) use ($app) {
	if ($app['debug']) {
		return null;
	}

	throw $e;
});

$app->get('/', function () use ($app) {
	return $app->redirect('/welcome');
});

$app->get('/welcome', function (CmsApplication $app) {
	return $app->render('welcome.twig');
});

$app->get('/login', function (CmsApplication $app) {
	LoginService::resetSession();

	return $app->render('login.twig');
});

$app->post('/login', function (Request $req) {
	$id = $req->get('id');
	$passwd = $req->get('passwd');
	$return_url = $req->get('return_url', 'welcome');

	try {
		$login_service = new LoginService();
		$login_service->doLoginAction($id, $passwd);

		return RedirectResponse::create($return_url);
	} catch (Exception $e) {
		return UrlHelper::printAlertRedirect('/login?return_url=' . urlencode($return_url), $e->getMessage());
	}
});

$app->get('/logout', function () {
	LoginService::resetSession();
	return RedirectResponse::create('/');
});

$app->mount('/super', new SuperControllerProvider());
$app->mount('/comm', new UserControllerProvider());

$app->run();
