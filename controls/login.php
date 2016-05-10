<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminTagSessionOperator;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Symfony\Component\HttpFoundation\Request;

//TODO Session을 관리하는 Util class 하나 만들까
$_SESSION['session_admin_id'] = null;
$_SESSION['session_user_auth'] = null;
$_SESSION['session_user_menu'] = null;
$_SESSION['session_user_tag'] = null;
$_SESSION['session_user_tagid'] = null;
//Warning: session_destroy(): Session object destruction failed
@session_destroy();

$req = Request::createFromGlobals();
$cmd = $req->get("cmd", "");
$id = $req->get("id", "");
$passwd = $req->get("passwd", "");
$return_url = $req->get("return_url", "welcome");

$login_service = new LoginService();

if ($cmd) {
	try {
		$login_service->doLoginAction($id, $passwd);

		if (AdminTagSessionOperator::isPart1stCheck()) {
			$return_url = "/admin/book/productList?type=1stCompleted";
		} elseif (AdminTagSessionOperator::isPart2ndCheck()) {
			$return_url = "/admin/book/productList?type=2ndCompleted";
		} elseif (AdminTagSessionOperator::isPartMake()) {
			$return_url = "/admin/book/productList?type=scheduled";
		} elseif (AdminTagSessionOperator::isPartRegister()) {
			$return_url = "/admin/book/productList?type=received";
		} elseif (AdminTagSessionOperator::isPartPrincipal()) {
			$return_url = "/admin/book/withholdList?type=withhold";
		}

		UrlHelper::redirect($return_url);
	} catch (Exception $e) {
		return UrlHelper::printAlertRedirect("/login?return_url=" . urlencode($return_url), $e->getMessage());
	}
}

return [];
