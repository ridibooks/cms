<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminAuthService;
use Ridibooks\Platform\Cms\CmsApplication;
use Symfony\Component\HttpFoundation\Request;

function selfRouting($twig_path, $twig_args = [])
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

	$login_url = '/admin/login';
	$on_login_page = (strncmp($_SERVER['REQUEST_URI'], $login_url, strlen($login_url)) === 0);

	if ($on_login_page) {
		if (\Config::$ENABLE_SSL && !onHttps($request)) {
			$request_uri = $request->server->get('REQUEST_URI');

			if (!empty($request_uri) && $request_uri != $login_url) {
				$request_uri = str_replace('/admin/login?return_url=', '', $request_uri);
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

		$should_https = Config::$ENABLE_SSL && AdminAuthService::isSecureOnlyUri();

		if (!onHttps($request) && $should_https) {
			UrlHelper::redirectHttps($_SERVER['REQUEST_URI']);
		} elseif (onHttps($request) && !$should_https) {
			$redirect = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			UrlHelper::redirect($redirect);
		}
	}

	$return_value = callController($query);

	return callView($query, $twig_path, array_merge($twig_args, $return_value));
}

/**
 * @param Request $request
 * @return bool
 */
function onHttps($request)
{
	return ($request->isSecure()
		|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'));
}

function callController($query)
{
	$controller_file_path = Env::$HOST_ROOT . "/controls/" . $query . ".php";

	if (!is_file($controller_file_path)) {
		return false;
	}

	// Controller 호출
	$return_value = include($controller_file_path);

	if (!is_array($return_value)) {
		exit($return_value);
	}

	// View 처리
	return $return_value;
}

function callView($query, $twig_path, $twig_args)
{
	$view_file_name = $query . '.twig';
	if (!is_file($twig_path . '/' . $view_file_name)) {
		return false;
	}

	$app = new CmsApplication();
	$app['twig.path'] = [
		$twig_path,
		__DIR__ . '/../views/'
	];
	$app['twig.env.globals'] = $twig_args;

	/** @var \Twig_Environment $twig_helper */
	$twig_helper = $app['twig'];
	$twig_helper->display($view_file_name);

	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Config::$ENABLE_DB_LOGGER) {
		echo \Ridibooks\Library\DB\Profiler::getInstance()->buildQueryHtml();
	}

	return true;
}
