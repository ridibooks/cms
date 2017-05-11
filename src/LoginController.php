<?php

namespace Ridibooks\Cms;

use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Service\LoginService;
use Ridibooks\Platform\Cms\Util\UrlHelper;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller_collection = $app['controllers_factory'];

		// login page
		$controller_collection->get('/login', [$this, 'getLoginPage']);

		// login process
		$controller_collection->post('/login-cms', [$this, 'loginWithCms']);
		$controller_collection->get('/login-azure', [$this, 'loginWithAzure']);

		// logout
		$controller_collection->get('/logout', [$this, 'logout']);

		return $controller_collection;
	}

	public function getLoginPage(Request $request, CmsServerApplication $app)
	{
		$azure_config = $app['azure'];
		$end_point = AzureOAuth2Service::getAuthorizeEndPoint($azure_config);
		$return_url = $request->get('return_url');

		if (!$return_url) {
			$return_url = '/welcome';
		}

		$response = Response::create();
		$response->headers->setCookie(new Cookie('return_url', $return_url));

		return $app->render('login.twig', [
			'azure_login' => $end_point
		], $response);
	}

	public function loginWithCms(Request $request, Application $app)
	{
		$id = $request->get('id');
		$passwd = $request->get('passwd');
		$return_url = $request->cookies->get('return_url');

		try {
			LoginService::doLoginAction($id, $passwd);

			$response = Response::create(UrlHelper::printAlertRedirect($return_url, "로그인 시 ID/PW 입력 방식은 곧 사라지고 메일 인증 방식만 남을 예정입니다.\n메일 인증 방식을 이용해보지 않은 분들은 그전까지 한번 테스트 부탁드립니다.\n(로그인 화면에서 Sign in with Azure 버튼 클릭)"));
			$response->headers->clearCookie('return_url');
            return $response;

		} catch (\Exception $e) {
			return UrlHelper::printAlertHistoryBack($e->getMessage());
		}
	}

	public function loginWithAzure(Request $request, Application $app)
	{
		$code = $request->get('code');
		$return_url = $request->cookies->get('return_url');

		if (!$code) {
			$error = $request->get('error');
			$error_description = $request->get('error_description');

			// TODO: send log to sentry
			return Response::create('azure login fail', Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		try {
			$azure_config = $app['azure'];
			$resource = AzureOAuth2Service::getResource($code, $azure_config);

			LoginService::doLoginActionWithoutPasswd($resource->mailNickname);

			$response = RedirectResponse::create($return_url);
			$response->headers->clearCookie('return_url');

			return $response;
		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect($return_url, $e->getMessage());
		}
	}

	public function logout()
	{
		LoginService::resetSession();
		return RedirectResponse::create('/login');
	}
}
