<?php
use Ridibooks\Library\TwigHelper;
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminAuthService;
use Symfony\Component\HttpFoundation\Request;

function selfRouting()
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

	return callController($query);
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
	return callView($query, $return_value);
}

function callView($query, $return_value)
{
	$view_file_name = $query . '.twig';
	if (!is_file(Env::$HOST_ROOT . "/views/" . $view_file_name)) {
		return false;
	}

	global $TWIG_ARGS;

	$TWIG_ARGS['FRONT_URL'] = 'http://' . Config::$DOMAIN;
	$TWIG_ARGS['STATIC_URL'] = '/admin/static';
	$TWIG_ARGS['MISC_URL'] = Config::$MISC_URL;
	$TWIG_ARGS['BANNER_URL'] = Config::$ACTIVE_URL . '/ridibooks_banner/';
	$TWIG_ARGS['ACTIVE_URL'] = Config::$ACTIVE_URL;
	$TWIG_ARGS['DM_IMAGE_URL'] = Config::$ACTIVE_URL . '/ridibooks_dm/';

	$TWIG_ARGS['PHP_SELF'] = $_SERVER['PHP_SELF'];
	$TWIG_ARGS['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

	$TWIG_ARGS["HTTP_HOST_LINK"] = Config::$HTTP_HOST_LINK;
	$TWIG_ARGS["SSL_HOST_LINK"] = Config::$SSL_HOST_LINK;

	//for compatible
	$TWIG_ARGS['base_url'] = Config::$DOMAIN;

	$TWIG_ARGS['session_user_menu'] = $_SESSION['session_user_menu'];

	$args = array_merge($TWIG_ARGS, $return_value);

	$twig_helper = TwigHelper::createForAdmin();
	$twig_helper->addFunction(new Twig_SimpleFunction('strtotime', 'strtotime'));
	$twig_helper->addFilter(new Twig_SimpleFilter('strtotime', 'strtotime'));
	$twig_helper->addFilter(
		new Twig_SimpleFilter('filterBookdesc', 'filterBookdesc', ['is_safe' => ['html']])
	);

	$twig_helper->loadTemplate($view_file_name)->display($args);

	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Config::$ENABLE_DB_LOGGER) {
		echo \Ridibooks\Library\DB\Profiler::getInstance()->buildQueryHtml();
	}

	return true;
}
