<?php
namespace Ridibooks\Cms;

use Ridibooks\Cms\Service\AdminAuthService;
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
	 * @return null|Response
	 */
	public static function shouldRedirectForLogin(Request $request, $enable_ssl)
	{
		// thrift request
		if ($request->getMethod() === 'POST' && $request->getRequestUri() === '/') {
			return null;
		}

        $response = self::conformAllowedProtocol($request, $enable_ssl);
        if ($response) {
            return $response;
        }

		if (self::onLoginPage($request)) {
			return null;
		}

		$login_required_response = AdminAuthService::authorize($request);
		if ($login_required_response !== null) {
			return $login_required_response;
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
	private static function onLoginPage($request)
	{
		$login_url = '/login';
		return strncmp($request->getRequestUri(), $login_url, strlen($login_url)) === 0;
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
