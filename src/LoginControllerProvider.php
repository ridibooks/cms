<?php

namespace Ridibooks\Platform\Cms;

use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller_collection = $app['controllers_factory'];
		$controller_collection->get('/', [$this, 'index']);
		$controller_collection->get('/welcome', [$this, 'getWelcomePage']);
		$controller_collection->get('/login', [$this, 'getLoginPage']);
		$controller_collection->post('/login', [$this, 'loginWithCms']);
		$controller_collection->get('/login.azure', [$this, 'loginWithAzure']);
		$controller_collection->get('/logout', [$this, 'logout']);
		return $controller_collection;
	}

	public function index(Request $request, CmsApplication $app)
	{
		return $app->redirect('/welcome');
	}

	public function getWelcomePage(Request $request, CmsApplication $app)
	{
		return $app->render('welcome.twig');
	}

	public function getLoginPage(Request $request, CmsApplication $app)
	{
		LoginService::resetSession();

		$login_endpoint = $app['cms'].$app['login_path'];
		$callback_path = '/login.azure';
		$return_path = $request->get('return_url');

		$end_point = LoginService::getCmsLoginEndPoint($login_endpoint, $callback_path, $return_path);
		return $app->render('login.twig', ['azure_login' => $end_point]);
	}

	public function loginWithCms(Request $request, CmsApplication $app)
	{
		$id = $request->get('id');
		$passwd = $request->get('passwd');
		$return_url = $request->get('return_url', '/welcome');

		try {
			LoginService::doLoginAction($id, $passwd);
			return RedirectResponse::create($return_url);

		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect('/login?return_url='.urlencode($return_url), $e->getMessage());
		}
	}

	public function loginWithAzure(Request $request, CmsApplication $app)
	{
		$resource = $request->get('resource');
		$return_url = $request->get('return_url', '/welcome');
		$resource = json_decode(urldecode($resource));

		try {
			LoginService::doAzureLoginAction($resource);
			return RedirectResponse::create($return_url);

		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect('/login?return_url='.urlencode($return_url), $e->getMessage());
		}
	}

	public function logout(Request $request, CmsApplication $app)
	{
		LoginService::resetSession();
		return RedirectResponse::create('/');
	}
}
