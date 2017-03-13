<?php

namespace Ridibooks\Cms;

use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Thrift\ThriftResponse;
use Ridibooks\Library\UrlHelper;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ridibooks\Cms\Service\AdminUserService;
use Ridibooks\Platform\Cms\Auth\PasswordService;

class CmsServerController implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller_collection = $app['controllers_factory'];

		//thrift
		$controller_collection->post('/', [$this, 'processThrift']);

		//login
		$controller_collection->get('/login', [$this, 'getLoginPage']);
		$controller_collection->post('/login.cms', [$this, 'loginWithCms']);
		$controller_collection->post('/login.azure', [$this, 'loginWithAzure']);

		//azure login callback
		$controller_collection->get('/login.azure', [$this, 'azureLoginCallback']);
		$controller_collection->get('/logout.azure', [$this, 'azureLogoutCallback']);

		//user info
		$controller_collection->get('/me', [$this, 'getMyInfoPage']);

		//document
		$controller_collection->get('/', [$this, 'index']);
		$controller_collection->get('/menu', [$this, 'menu']);
		$controller_collection->get('/tag', [$this, 'tag']);
		$controller_collection->get('/user', [$this, 'user']);

		return $controller_collection;
	}

	public function processThrift(Request $request)
	{
		return ThriftResponse::create($request);
	}

	public function getLoginPage(Request $request, Application $app)
	{
		$azure_config = $app['azure'];
		$end_point = AzureOAuth2Service::getAuthorizeEndPoint($azure_config);
		$callback = $request->get('callback');
		$return_url = $request->get('return_url');
		return $app->render('login.twig', [
			'callback' => $callback,
			'return_url' => $return_url,
			'azure_login' => $end_point
		]);
	}

	public function loginWithCms(Request $request, Application $app)
	{
		$id = $request->get('id');
		$passwd = $request->get('passwd');
		$callback = urldecode($request->get('callback'));
		$return_url = urldecode($request->get('return_url'));

		try {
			$userService = new AdminUserService();
			$user = $userService->getUser($id);
			if (!$user || $user->is_use != '1') {
				throw new \Exception('잘못된 계정정보입니다.');
			}

			if (!PasswordService::isPasswordMatchToHashed($passwd, $user->passwd)) {
				throw new \Exception('비밀번호가 맞지 않습니다.');
			}

			if (PasswordService::needsRehash($user->passwd)) {
				$userService->updatePassword($id, $passwd);
			}

			$cipher = $this->encodeResource($user->id, $app['login_encrypt_key']);
			$redirect_url = $callback . '?resource=' . urlencode($cipher);
			if ($return_url) {
				$redirect_url .= '&return_url=' . $return_url;
			}

			$response = RedirectResponse::create($redirect_url);

			return $response;
		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect('/login?callback='.urlencode($callback).'&return_url='.urlencode($return_url), $e->getMessage());
		}
	}

	public function loginWithAzure(Request $request, Application $app)
	{
		$azure_config = $app['azure'];
		$response = RedirectResponse::create(AzureOAuth2Service::getAuthorizeEndPoint($azure_config));
		$callback = $request->get('callback');
		$return_url = $request->get('return_url');
		$response->headers->setCookie(new Cookie('callback', $callback));
		$response->headers->setCookie(new Cookie('return_url', $return_url));
		return $response;
	}

	private function encodeResource($resource, $key)
	{
		$method = 'aes-256-ctr';
		$nonceSize = openssl_cipher_iv_length($method);
		$nonce = openssl_random_pseudo_bytes($nonceSize);
		$ciphertext = openssl_encrypt($resource, $method, $key, OPENSSL_RAW_DATA, $nonce);
		return $nonce.$ciphertext;
	}

	public function azureLoginCallback(Request $request, Application $app)
	{
		$code = $request->get('code');
		$callback = urldecode($request->cookies->get('callback'));
		$return_url = urldecode($request->cookies->get('return_url'));

		if (!$code) {
			$error = $request->get('error');
			$error_description = $request->get('error_description');
			return UrlHelper::printAlertRedirect($callback, "$error: $error_description");
		}

		try {
			$azure_config = $app['azure'];
			$resource = AzureOAuth2Service::getResource($code, $azure_config);
			$cipher = $this->encodeResource($resource->mailNickname, $app['login_encrypt_key']);
			$redirect_url = $callback . '?resource=' . urlencode($cipher);
			if ($return_url) {
				$redirect_url .= '&return_url=' . $return_url;
			}
			$response = RedirectResponse::create($redirect_url);
			$response->headers->setCookie(new Cookie('callback', '', time() - 3600));
			$response->headers->setCookie(new Cookie('return_url', '', time() - 3600));

			return $response;
		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect($return_url, $e->getMessage());
		}
	}

	public function azureLogoutCallback(Request $request)
	{
		return Response::create('success', Response::HTTP_OK);
	}

	public function index()
	{
		return RedirectResponse::create('/static/docs/index.html');
	}

	public function menu()
	{
		return RedirectResponse::create('/static/docs/AdminMenu.html');
	}

	public function tag()
	{
		return RedirectResponse::create('/static/docs/AdminTag.html');
	}

	public function user()
	{
		return RedirectResponse::create('/static/docs/AdminUser.html');
	}
}
