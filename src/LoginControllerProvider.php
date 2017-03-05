<?php

namespace Ridibooks\Platform\Cms;

use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\Lib\AzureOAuth2Service;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

		//$azure_config = $app['azure'];
		//$end_point = AzureOAuth2Service::getAuthorizeEndPoint($azure_config);

		//thrift to cms server: getAuthorizeEndPost()
		//Todo
		//$end_point = Thrift::getAuthorizeEndPost();
		$end_point = '/welcome';
		return $app->render('login.twig', ['azure_login' => $end_point]);

		//redirect to cms server
	}

	public function loginWithCms(Request $request, CmsApplication $app)
	{
		$id = $request->get('id');
		$passwd = $request->get('passwd');
		$return_url = $request->get('return_url', 'welcome');

		try {
			$login_service = new LoginService();
			$login_service->doLoginAction($id, $passwd);

			return RedirectResponse::create($return_url);
		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect('/login?return_url=' . urlencode($return_url), $e->getMessage());
		}
	}

	public function loginWithAzure(Request $request, CmsApplication $app)
	{
		$code = $request->get('code');
		$return_url = $request->get('return_url', 'welcome');

		if (!$code) {
			$error = $request->get('error');
			$error_description = $request->get('error_description');
			return UrlHelper::printAlertRedirect('/login?return_url=' . urlencode($return_url), "$error: $error_description");
		}

		try {
			$azure_config = $app['azure'];
			$login_service = new LoginService();
			$login_service->doAzureLoginAction($code, $azure_config);
			return RedirectResponse::create($return_url);

		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect('/login?return_url=' . urlencode($return_url), $e->getMessage());
		}
	}

	public function logout(Request $request, CmsApplication $app)
	{
		LoginService::resetSession();
		return RedirectResponse::create('/');
	}
}
