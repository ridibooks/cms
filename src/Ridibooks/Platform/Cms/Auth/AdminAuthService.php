<?php

namespace Ridibooks\Platform\Cms\Auth;

use Ridibooks\Exception\MsgException;
use Ridibooks\Library\UrlHelper;
use Ridibooks\Library\Util;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Cms\Auth\Model\AdminMenuAjaxs;
use Ridibooks\Platform\Cms\Auth\Model\AdminTagMenus;
use Ridibooks\Platform\Cms\Auth\Model\AdminUserMenus;
use Ridibooks\Platform\Cms\Auth\Model\AdminUserTags;
use Ridibooks\Platform\Cms\Auth\Model\TbAdminUserModel;
use Ridibooks\Platform\Common\Base\AdminBaseService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**권한 설정 Service
 * Class AdminAuthService
 * @package Ridibooks\Platform\Cms\Auth
 */
class AdminAuthService extends AdminBaseService
{
	/**
	 * @var MenuService
	 */
	private $menuService;
	/**
	 * @var AdminUserMenus
	 */
	private $adminUserMenus;
	/**
	 * @var AdminMenuAjaxs
	 */
	private $adminMenuAjaxs;
	/**
	 * @var AdminTagMenus
	 */
	private $adminTagMenus;
	/**
	 * @var AdminUserTags
	 */
	private $adminUserTags;

	private $adminAuth; //권한이 있는 메뉴 array
	private $adminMenu; //권한이 없는 순수 메뉴 array
	private $adminTag; //로그인 한 유저의 Tag Array
	private $isCache = false; //세션을 사용한 cache를 하지 않는다.

	public function __construct()
	{
		if (!$this->isCache || !isset($_SESSION['session_user_auth']) || count($_SESSION['session_user_auth']) == 0) {
			// session_user_auth가 session에 없을 경우에만 query
			$this->initService();
			$this->initAdminAuth();
			$this->initAdminMenu();
			$this->initAdminTag();
		}
	}

	/**init classes*/
	private function initService()
	{
		$this->menuService = new MenuService();
		$this->adminUserMenus = new AdminUserMenus();
		$this->adminMenuAjaxs = new AdminMenuAjaxs();
		$this->adminTagMenus = new AdminTagMenus();
		$this->adminUserTags = new AdminUserTags();
	}

	/**해당 유저의 모든 권한을 셋팅한다.*/
	private function initAdminAuth()
	{
		//전체 menu_ajax를 가지고 온다.
		$menu_ajax_array = $this->adminMenuAjaxs->getAdminMenuAjaxList();
		//전체 menu를 가져온다. (권한을 위해서 사용여부 상관없이 모두 가져온다.)
		$menu_array = $this->menuService->getMenuList();

		$auth_list = [];
		$menus_by_id = [];
		$menuids_by_url = [];
		$menu_id_array = [];

		foreach ($menu_array as $menu) {
			$menuid = $menu['id'];
			$url = $this->getUrlFromMenuUrl($menu);
			$menus_by_id[$menuid] = $menu;
			$menuids_by_url[$url][] = $menuid;
			$menu_id_array[] = $menuid;
		}


		if (\Config::$UNDER_DEV) {
			//개발 모드일 경우 모든 메뉴 id array 가져온다.
			$menuids_owned = $menu_id_array;
		} else {
			//로그인 한 유저의 메뉴 id array 가져온다.
			$menuids_owned = $this->getUserAllMenuId();
		}

		foreach ($menus_by_id as $menu) {
			$menuid = $menu['id'];
			$url = $this->getUrlFromMenuUrl($menu);
			$depth = $menu['menu_deep'];

			if ($depth == 0 && strlen($url) == 0) {
			} elseif (in_array($menuid, $menuids_owned)) {
				//get ajaxs
				$menu['ajax_array'] = $this->makeAjaxMenuArray($menuid, $menu_ajax_array);

				//get auths(hashes)
				$auths = [];
				if ($menuids_by_url[$url]) {
					$menuids_of_owned_auth = array_intersect($menuids_by_url[$url], $menuids_owned);

					foreach ($menuids_of_owned_auth as $menuid_by_owned_auth) {
						$auths[] = self::makeMenuAuth($menus_by_id[$menuid_by_owned_auth]['menu_url']);
					}
					$auths = array_unique(array_filter($auths));
				}
				$menu['auth'] = $auths;
			} else {
				continue;
			}

			//insert
			$auth_list[] = $menu;
		}

		//비어있는 최상위 메뉴는 안보이게
		foreach ($auth_list as $key => $menu) {
			$current_url = $this->getUrlFromMenuUrl($menu);
			$current_depth = $menu['menu_deep'];
			$currrent_menu_is_top = ($current_depth == 0 && strlen($current_url) == 0);

			//이전 메뉴와 비교
			if ($key != 0) {
				$last_key = $key - 1;
				$last_menu = $auth_list[$last_key];

				$prev_url = $this->getUrlFromMenuUrl($last_menu);
				$prev_depth = $last_menu['menu_deep'];
				$prev_menu_is_top = ($prev_depth == 0 && strlen($prev_url) == 0);

				if ($prev_menu_is_top && $currrent_menu_is_top) {
					$auth_list[$last_key]['is_show'] = false;
				}
			}

			//tail 체크
			if ($key == count($auth_list) - 1 && $currrent_menu_is_top) {
				$auth_list[$key]['is_show'] = false;
			}
		}

		$this->adminAuth = $auth_list;
	}

	/**해당 유저의 메뉴를 셋팅한다.
	 */
	private function initAdminMenu()
	{
		$admin_menu = [];
		foreach ($this->adminAuth as $menu) {
			if ($menu['is_use'] == 1 && $menu['is_show'] == 1) {
				$admin_menu[$menu['id']] = $menu;
			}
		}
		$this->adminMenu = $admin_menu;
	}

	/**해당 유저의 태그를 셋팅한다.
	 */
	private function initAdminTag()
	{
		$this->adminTag = $this->adminUserTags->getAdminUserTagList(LoginService::GetAdminID());
	}

	/**로그인한 유저의 모든 메뉴Id를 가져온다.
	 * @return array menu_id array
	 */
	private function getUserAllMenuId()
	{
		//해당 유저에 매핑되어 있는 tag_id를 가져온다.
		$tag_id_array = $this->adminUserTags->getAdminUserTagList(LoginService::GetAdminID());
		$menu_id_array = [];
		foreach ($tag_id_array as $tag_id) { //한 사람에게 여러개의 tag가 붙을 수 있기에...
			//tag_id를 통해서 매핑되어있는 menu_id를 가져온다.
			$menu_tag_list = $this->adminTagMenus->getAdminMenuTagList($tag_id);
			foreach ($menu_tag_list as $menu_tag) {
				array_push($menu_id_array, $menu_tag['menu_id']);
			}
		}
		//해당 유저에 매핑되어 있는 menu_id 가져온다.
		$user_menu_array = $this->adminUserMenus->getAdminUserMenuList(LoginService::GetAdminID());
		foreach ($user_menu_array as $user_menu) {
			array_push($menu_id_array, $user_menu);
		}
		return array_unique($menu_id_array);
	}

	/**menu ajax array 만든다.
	 * @param $menu_id
	 * @param $menu_ajax_array
	 * @return array menu ajax array
	 */
	private function makeAjaxMenuArray($menu_id, $menu_ajax_array)
	{
		$ajax_array = [];
		//해당 menu 내의 ajax 리스트가 있는지 확인한다.
		foreach ($menu_ajax_array as $menu_ajax) {
			if ($menu_ajax['menu_id'] == $menu_id) { //매핑되어 있는 menu가 ajax를 가지고 있을 경우
				$menu_ajax['ajax_auth'] = self::makeMenuAuth($menu_ajax['ajax_url']);
				array_push($ajax_array, $menu_ajax);
			}
		}
		return $ajax_array;
	}

	/**url에 #태그 확인하여 권한을 반환한다.
	 * @param $menu_url
	 * @return null
	 */
	private static function makeMenuAuth($menu_url)
	{
		$menuUrl = preg_split('/#/', $menu_url);
		return (isset($menuUrl[1]) && !is_null($menuUrl[1])) ? $menuUrl[1] : null;
	}

	/**권한이 정확한지 확인
	 * @param null $hash
	 * @param $auth
	 * @return bool
	 */
	private static function isAuthCorrect($hash, $auth)
	{
		if (is_null($hash)) { //hash가 없는 경우 (보기 권한)
			return true;
		} elseif (is_array($hash)) { //hash가 array인 경우
			foreach ($hash as $h) {
				if (in_array($h, $auth)) {
					return true;
				}
			}
		} elseif (is_array($auth) && in_array($hash, $auth)) {
			return true;
		} elseif ($auth == $hash) {
			return true;
		}
		return false;
	}

	/**입력받은 url이 권한을 가지고 있는 url인지 검사<br/>
	 * '/comm/'으로 시작하는 url은 권한을 타지 않는다. (개인정보 수정 등 로그인 한 유저가 공통적으로 사용할 수 있는 기능을 /comm/에 넣을 예정)
	 * @param $check_url
	 * @param $menu_url
	 * @return bool
	 */
	private static function isAuthUrl($check_url, $menu_url)
	{
		$auth_url = preg_replace('/(\?|#).*/', '', $menu_url);
		if (strpos($check_url, '/comm/')) { // /comm/으로 시작하는 url은 권한을 타지 않는다.
			return true;
		}
		if ($auth_url != '' && strpos($check_url, $auth_url) !== false) { //현재 url과 권한 url이 같은지 비교
			return true;
		}
		return false;
	}

	/**
	 * @param $check_url
	 * @param $auths
	 * @return array
	 */
	public static function getHashesFromMenus($check_url, $auths)
	{
		$hash_array = [];
		foreach ($auths as $auth) {
			if (self::isAuthUrl($check_url, $auth['menu_url'])) {
				if ($auth['auth']) {
					$hash_array = array_merge($hash_array, $auth['auth']);
				} else {
					$hash_array[] = self::makeMenuAuth($auth['menu_url']);
				}
			}

			if (isset($auth['ajax_array'])) { //해당 session_user_auth row에 ajax_array가 있는지 확인
				foreach ($auth['ajax_array'] as $ajax) { // ajax_array 내의 key(ajax_url, ajax_auth)
					if (self::isAuthUrl($check_url, $auth['menu_url'])) {
						$hash_array = array_merge($hash_array, $auth['auth']);
					}
					if (self::isAuthUrl($check_url, $ajax['ajax_url'])) {
						$hash_array = array_merge($hash_array, $ajax['ajax_auth']);
					}
				}
			}
		}
		$hash_array = array_filter(array_unique($hash_array));
		return $hash_array;
	}

	/**해당 유저의 모든 권한을 가져온다.
	 * @return array
	 */
	public function getAdminAuth()
	{
		return $this->adminAuth;
	}

	/**해당 유저가 볼 수 있는 메뉴를 가져온다.
	 * @return array
	 */
	public function getAdminMenu()
	{
		return $this->adminMenu;
	}

	/**해당 유저의 모든 태그를 가져온다.
	 * @return array
	 */
	public function getAdminTag()
	{
		return $this->adminTag;
	}

	/**해당 유저의 태그 ID 가져온다.
	 * @return array
	 */
	public function getAdminTagId()
	{
		$session_user_tagid = [];
		foreach ($_SESSION['session_user_tag'] as $tag) {
			$session_user_tagid[] = $tag;
		}
		return $session_user_tagid;
	}

	/**해당 URL에 접근할 권한이 있는지 검사한다.<br/>
	 * 문제점
	 * - 각 menu 밑에 sub url 검사를 한번 더 하는데 의존관계를 알기 힘들다.
	 * - 현재는 여러 페이지에서 사용하는 ajax_url의 권한을 확실하게 하지 못한다.
	 * - 나중에 권한을 좀 더 세분화 시킬때는 sub url을 unique키로 하여 각 sub url의 진입점을 구분하도록 메뉴주소를 따로 구분하는게 좋을것 같다.
	 * @param null $method
	 * @param null $check_url
	 * @throws
	 */
	public static function hasUrlAuth($method = null, $check_url = null)
	{
		if (!self::hasHashAuth($method, $check_url) && !\Config::$UNDER_DEV) {
			throw new MsgException("해당 권한이 없습니다.");
		}
	}

	/**해당 URL의 Hash 권한이 있는지 검사한다.<br/>
	 * @param null $hash
	 * @param null $check_url
	 * @return bool
	 */
	public static function hasHashAuth($hash = null, $check_url = null)
	{
		if (!isset($check_url) || trim($check_url) === '') {
			$check_url = $_SERVER['REQUEST_URI'];
		}

		$allowed_urls = [
			'/admin/welcome',	// deprecated
			'/welcome',
			'/logout',
			'/'
		];

		// welcome 페이지는 항상 허용
		if (in_array($check_url, $allowed_urls)) {
			return true;
		}

		foreach ($_SESSION['session_user_auth'] as $auth) {
			if (self::isAuthUrl($check_url, $auth['menu_url'])) {
				if (self::isAuthCorrect($hash, (isset($auth['auth']) ? $auth['auth'] : []))) {
					return true;
				}
			}
			if (isset($auth['ajax_array'])
				&& !is_null($auth['ajax_array'])
			) { //해당 session_user_auth row에 ajax_array가 있는지 확인
				foreach ($auth['ajax_array'] as $ajax) { // ajax_array 내의 key(ajax_url, ajax_auth)
					if (self::isAuthUrl($check_url, $ajax['ajax_url'])) {
						if (self::isAuthCorrect($hash, $auth['auth'])) {
							return true;
						}
						if (self::isAuthCorrect($hash, $ajax['ajax_auth'])) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	/**해당 URL의 Hash 권한 Array를 반환한다.
	 * @param null $check_url
	 * @return array $hash_array
	 */
	public static function getCurrentHashArray($check_url = null)
	{
		if (!isset($check_url) || trim($check_url) === '') {
			$check_url = $_SERVER['REQUEST_URI'];
		}

		$auths = $_SESSION['session_user_auth'];
		if (!is_array($auths)) {
			$auths = [];
		}
		$hash_array = self::getHashesFromMenus($check_url, $auths);
		return $hash_array;
	}

	/**적합한 IP인지 검사한다.
	 * @return bool
	 */
	public static function isValidIp()
	{
		return Util::isRidiIP();
	}

	/**적합한 로그인 상태인지 검사한다.
	 * @return bool
	 */
	public static function isValidLogin()
	{
		return LoginService::GetAdminID()
			&& isset($_SESSION['session_user_auth']) && isset($_SESSION['session_user_menu']);
	}

	/**적합한 유저인지 검사한다.
	 * @return bool
	 */
	public static function isValidUser()
	{
		$adminUserDto = new AdminUserDto(TbAdminUserModel::getAdminUser(LoginService::GetAdminID()));

		return $adminUserDto->is_use ? true : false;
	}

	/**
	 * @param $menu_raw
	 * @return mixed
	 */
	private function getUrlFromMenuUrl($menu_raw)
	{
		$url = preg_replace('/#.*/', '', $menu_raw['menu_url']);
		return $url;
	}

	public static function initSession()
	{
		// 세션 변수 설정
		$auth_service = new AdminAuthService();
		$_SESSION['session_user_auth'] = $auth_service->getAdminAuth();
		$_SESSION['session_user_menu'] = $auth_service->getAdminMenu();
		$_SESSION['session_user_tag'] = $auth_service->getAdminTag();
		$_SESSION['session_user_tagid'] = $auth_service->getAdminTagId();
	}

	/**
	 * @param Request $request
	 * @return null|Response
	 */
	public static function authorize($request)
	{
		if (!\Config::$UNDER_DEV && !AdminAuthService::isValidIp()) {
			return new Response(
				UrlHelper::printAlertRedirect(
					'http://' . \Config::$DOMAIN,
					'허가된 IP가 아닙니다.'
				)
			);
		}

		if (!AdminAuthService::isValidLogin() || !AdminAuthService::isValidUser()) {
			$login_url = '/login';
			$request_uri = $request->getRequestUri();
			if (!empty($request_uri) && $request_uri != '/login') {
				$login_url .= '?return_url=' . urlencode($request_uri);
			}

			$protocol = \Config::$ENABLE_SSL ? 'https' : 'http';

			return RedirectResponse::create($protocol . '://' . $request->getHttpHost() . $login_url);
		}

		try {
			AdminAuthService::hasUrlAuth();
		} catch (\Exception $e) {
			// 이상하지만 기존과 호환성 맞추기 위해
			if ($request->isXmlHttpRequest()) {
				return new Response($e->getMessage());
			} else { //일반 페이지
				return new Response(UrlHelper::printAlertHistoryBack($e->getMessage()));
			}
		}

		return null;
	}

	public static function isSecureOnlyUri($request_uri)
	{
		$secure_prefix = ['/stat/', '/bi/', '/store-operation/', '/cs/', '/admin/comm/user_info'];
		foreach ($secure_prefix as $prefix) {
			if (strncmp($request_uri, $prefix, strlen($prefix)) === 0) {
				return true;
			}
		}

		return false;
	}
}
