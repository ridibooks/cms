<?php
namespace Ridibooks\Platform\Cms;

use Ridibooks\Library\DB\Profiler;
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminAuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiniRouter
{
	public static function selfRouting($controller_path, $twig_path, $twig_args = [])
	{
		$query = $_SERVER['QUERY_STRING'];

		$pattern = '/^([\w_\/\.]+)\&?(.*)$/';
		if (!preg_match($pattern, $query, $mat)) {
			return false;
		}

		// htaccess에서 mini_router.php?aaa/bbb?a=b 이런식으로 넘겨주는데 aaa/bbb를 GET과 QUERY_STRING에서 제거함
		$_SERVER['PHP_SELF'] = $GLOBALS['PHP_SELF'] = preg_replace('/\?.+/', '', $_SERVER['REQUEST_URI']);
		$query = $mat[1];
		$_SERVER['QUERY_STRING'] = $mat[2];

		unset($_GET[$query]);

		$request = Request::createFromGlobals();

		$login_url = '/login';
		$on_login_page = (strncmp($_SERVER['REQUEST_URI'], $login_url, strlen($login_url)) === 0);

		if ($on_login_page) {
			if (\Config::$ENABLE_SSL && !self::onHttps($request)) {
				$request_uri = $request->server->get('REQUEST_URI');

				if (!empty($request_uri) && $request_uri != $login_url) {
					$request_uri = str_replace('/login?return_url=', '', $request_uri);
					$login_url .= '?return_url=' . urlencode($request_uri);
				}

				UrlHelper::redirectHttps($login_url);
			}
		} else {
			AdminAuthService::initSession();
			$login_required = AdminAuthService::authorize($request);

			if ($login_required !== null) {
				$login_required->send();
				exit;
			}

			$should_https = \Config::$ENABLE_SSL && AdminAuthService::isSecureOnlyUri();

			if (!self::onHttps($request) && $should_https) {
				UrlHelper::redirectHttps($_SERVER['REQUEST_URI']);
			} elseif (self::onHttps($request) && !$should_https) {
				$redirect = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				UrlHelper::redirect($redirect);
			}
		}

		$return_value = self::callController($query, $controller_path);
		if (is_array($return_value)) {
			return self::callView($query, $twig_path, array_merge($twig_args, $return_value));
		} elseif ($return_value instanceof Response) {
			$return_value->send();

			return true;
		}

		return false;
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	private static function onHttps($request)
	{
		return ($request->isSecure()
			|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'));
	}

	private static function callController($query, $controller_path)
	{
		$controller_file_path = $controller_path . '/' . $query . ".php";
		if (!is_file($controller_file_path)) {
			return false;
		}

		// Controller 호출
		return include($controller_file_path);
	}

	private static function callView($query, $twig_path, $twig_args)
	{
		$view_file_name = $query . '.twig';
		if (!is_file($twig_path . '/' . $view_file_name)) {
			return false;
		}

		$app = new CmsApplication();
		$app['twig.path'] = [$twig_path];
		$app['twig.env.globals'] = $twig_args;

		/** @var \Twig_Environment $twig_helper */
		$twig_helper = $app['twig'];
		$twig_helper->display($view_file_name);

		if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && \Config::$ENABLE_DB_LOGGER) {
			echo Profiler::getInstance()->buildQueryHtml();
		}

		return true;
	}
}
