<?php
namespace Ridibooks\Platform\Cms;

use Ridibooks\Platform\Cms\Auth\AdminAuthService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiniRouter
{
	private $prefix_uri;
	private $controller_dir;
	private $view_dir;
	/**
	 * @var array
	 */
	private $global_args;

	public function __construct($controller_dir, $view_dir, $prefix_uri = '', $global_args = [])
	{
		$this->controller_dir = $controller_dir;
		$this->view_dir = $view_dir;
		$this->prefix_uri = self::getNormalizedUri($prefix_uri);
		$this->global_args = $global_args;
	}

	/**
	 * @param Request $request
	 * @param bool $enable_ssl
	 * @return Response
	 */
	public function route(Request $request, $enable_ssl = true)
	{
		$request_uri_wo_qs = self::getNormalizedUri($request->getRequestUri());

		// 기존 Router에서는 PHP_SELF와 Query String제외된 REQUEST_URI가 서로 같았으나
		// Routing 방식이 변경되면서 PHP_SELF는 이제 mini_router.php가 들어감
		// (PHP_SELF의 정확한 정의는 "The filename of the currently executing script, relative to the document root")
		// 하지만 CMS에서 해당 변수를 REQUEST_URI대신 사용하는 곳이 많아 호환성 위해 여기서 강제로 세팅
		$_SERVER['PHP_SELF'] = $request_uri_wo_qs;

		// 보안상 URL에 .나 ..가 있으면 무조건 404 표시
		if (preg_match('/\/\.+/', $request_uri_wo_qs) > 0) {
			return self::notFound();
		}

		if (empty($this->prefix_uri)) {
			$controller_path = $request_uri_wo_qs;
		} else {
			if (substr($request_uri_wo_qs, 0, strlen($this->prefix_uri)) !== $this->prefix_uri) {
				return self::notFound();
			}

			$controller_path = substr($request_uri_wo_qs, strlen($this->prefix_uri));
		}

		$response = self::shouldRedirectForLogin($request, $enable_ssl);
		if ($response) {
			return $response;
		}

		$return_value = $this->callController($controller_path);

		if (is_array($return_value)) {
			return $this->callView($request, $controller_path, $return_value);
		} elseif (is_string($return_value)) {
			return Response::create($return_value);
		} elseif ($return_value instanceof Response) {
			return $return_value;
		} elseif ($return_value === false) {
			return self::notFound();
		} else {
			// 리턴값이 아예 없는 페이지도 있어서 호환성 유지 위해
			return Response::create('', http_response_code());
		}
	}

	/**
	 * @param Request $request
	 * @param bool $enable_ssl
	 * @return null|Response
	 */
	public static function shouldRedirectForLogin(Request $request, $enable_ssl = true)
	{
		$response = self::conformAllowedProtocol($request, $enable_ssl);
		if ($response) {
			return $response;
		}

		if (self::onLoginPage($request)) {
			return null;
		}

		AdminAuthService::initSession();
		$login_required_response = AdminAuthService::authorize($request);

		if ($login_required_response !== null) {
			return $login_required_response;
		}

		return null;
	}

	private static function conformAllowedProtocol(Request $request, $enable_ssl)
	{
		if ($enable_ssl && !self::onHttps($request)) {
			return RedirectResponse::create('https://' . $request->getHttpHost() . $request->getRequestUri());
		} elseif (!$enable_ssl && self::onHttps($request)) {
			return RedirectResponse::create('http://' . $request->getHttpHost() . $request->getRequestUri());
		}

		return null;
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	private static function onHttps($request)
	{
		return $request->isSecure() || $request->server->get('HTTP_X_FORWARDED_PROTO') == 'https';
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	private static function onLoginPage($request)
	{
		$login_url = '/login';
		return strncmp($request->getRequestUri(), $login_url, strlen($login_url)) === 0;
	}

	private function callController($query)
	{
		$controller_file_path = $this->controller_dir . '/' . $query . ".php";
		if (!is_file($controller_file_path)) {
			return self::notFound();
		}

		return include($controller_file_path);
	}

	/**
	 * @param Request $request
	 * @param $query
	 * @param array $args
	 * @return Response
	 */
	private function callView($request, $query, $args)
	{
		$view_file_name = $query . '.twig';

		$app = new CmsApplication();
		$app['twig.path'] = [$this->view_dir];
		$app['twig.env.globals'] = $this->global_args;

		/** @var \Twig_Environment $twig_helper */
		$twig_helper = $app['twig'];

		return Response::create($twig_helper->render($view_file_name, $args));
	}

	private static function notFound()
	{
		return Response::create('<meta http-equiv="refresh" content="5;url=' . htmlspecialchars('http://' . $_SERVER['HTTP_HOST']) .
			'"> 페이지를 찾을 수 없습니다. URL이 변경되었을 수 있습니다. 오류라고 생각되시면 담당자에게 문의해 주세요.<br />' .
			'5초 후 자동으로 메인 페이지로 이동합니다.', Response::HTTP_NOT_FOUND);
	}

	/**
	 * @param $uri
	 * @return string trailing /, 중복 /, query string이 제거된 uri
	 */
	private static function getNormalizedUri($uri)
	{
		$normalized_uri = preg_replace('#/+#', '/', strtok($uri, '?'));
		if (substr($normalized_uri, 0, 1) !== '/') {
			$normalized_uri = '/' . $normalized_uri;
		}

		$normalized_uri = rtrim($normalized_uri, '/');

		return $normalized_uri;
	}
}
