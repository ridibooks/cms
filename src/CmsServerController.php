<?php

namespace Ridibooks\Cms;

use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Service\LoginService;
use Ridibooks\Library\UrlHelper;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CmsServerController implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller_collection = $app['controllers_factory'];

		// login page
		$controller_collection->get('/login/form', [$this, 'getLoginPage']);

		// login process
		$controller_collection->post('/login-cms', [$this, 'loginWithCms']);
		$controller_collection->get('/login-azure', [$this, 'loginWithAzure']);

		return $controller_collection;
	}

	public function getLoginPage(Request $request, Application $app)
	{
		$azure_config = $app['azure'];
		$end_point = AzureOAuth2Service::getAuthorizeEndPoint($azure_config);
		$return_url = $request->get('return_url');

		if (!$return_url) {
			return Response::create('Need a param: callback', Response::HTTP_BAD_REQUEST);
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

		$parsed = parse_url($return_url);
		$cookie_host = $parsed['host'];

		try {
			if (isset($app['couchbase']) && $app['couchbase']!=='') {
				$couchbase = $app['couchbase'];
				LoginService::startCouchbaseSession($couchbase['host'], $cookie_host);
			} else {
				LoginService::startSession($cookie_host);
			}

			LoginService::doLoginAction($id, $passwd);

			$response = RedirectResponse::create($return_url);
			$response->headers->clearCookie('callback');
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

		$parsed = parse_url($return_url);
		$cookie_host = $parsed['host'];

		if (!$code) {
			$error = $request->get('error');
			$error_description = $request->get('error_description');

			// TODO: send log to sentry
			return Response::create('azure login fail', Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		try {
			$azure_config = $app['azure'];
			$resource = AzureOAuth2Service::getResource($code, $azure_config);

			if (isset($app['couchbase']) && $app['couchbase']!=='') {
				$couchbase = $app['couchbase'];
				LoginService::startCouchbaseSession($couchbase['host'], $cookie_host);
			} else {
				LoginService::startSession($cookie_host);
			}

			LoginService::doLoginActionWithoutPasswd($resource->mailNickname);

			$response = RedirectResponse::create($return_url);
			$response->headers->clearCookie('callback');
			$response->headers->clearCookie('return_url');

			return $response;
		} catch (\Exception $e) {
			return UrlHelper::printAlertRedirect($return_url, $e->getMessage());
		}
	}
}
