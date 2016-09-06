<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminTagSessionOperator;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\Controller\SuperControllerProvider;
use Ridibooks\Platform\Cms\Controller\UserControllerProvider;
use Ridibooks\Platform\Cms\MiniRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/include/bootstrap_cms.php';

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

		if (AdminTagSessionOperator::isPart1stCheck()) {
			$return_url = '/admin/book2/productList?type=waitingOpen';
		} elseif (AdminTagSessionOperator::isPart2ndCheck()) {
			$return_url = '/admin/book2/productList?type=checkingCompleted';
		} elseif (AdminTagSessionOperator::isPartMake()) {
			$return_url = '/admin/book2/productList?type=scheduled';
		} elseif (AdminTagSessionOperator::isPartRegister()) {
			$return_url = '/admin/book2/productList?type=received';
		} elseif (AdminTagSessionOperator::isPartPrincipal()) {
			$return_url = '/admin/book/withholdList?type=withhold';
		}

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
