<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\Controller\AdminMenuControllerProvider;
use Ridibooks\Platform\Cms\Controller\AdminTagControllerProvider;
use Ridibooks\Platform\Cms\Controller\AdminUserControllerProvider;
use Ridibooks\Platform\Cms\Controller\UserControllerProvider;
use Ridibooks\Platform\Cms\MiniRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../include/config.php';
require __DIR__ . "/vendor/autoload.php";

LoginService::startSession();

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

$app->mount('/super', new AdminUserControllerProvider());
$app->mount('/super', new AdminTagControllerProvider());
$app->mount('/super', new AdminMenuControllerProvider());

$app->mount('/comm', new UserControllerProvider());

$app->run();
