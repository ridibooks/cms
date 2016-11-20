<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\MiniRouter;
use Ridibooks\Platform\Cms\UserControllerProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

$app->mount('/', new UserControllerProvider());

$app->run();
