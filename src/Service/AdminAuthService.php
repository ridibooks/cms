<?php

namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Thrift\AdminMenu\AdminMenu as ThriftAdminMenu;
use Ridibooks\Cms\Thrift\ThriftService;
use Ridibooks\Cms\Util\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**권한 설정 Service
 * @deprecated
 */
class AdminAuthService
{
    public function getAdminMenu(?string $user_id = null) : array
    {
        $user_service = new AdminUserService();
        $menus = $user_service->getAllMenus($user_id ?? LoginService::GetAdminID());

        $menus = $this->hideEmptyRootMenus($menus);

        $admin_menus = [];
        foreach ($menus as $menu) {
            if ($menu['is_use'] == 1 && $menu['is_show'] == 1) {
                $admin_menus[$menu['id']] = $menu;
            }
        }

        return array_map(function ($menu) {
            return new ThriftAdminMenu($menu);
        }, $admin_menus);
    }

    public static function isValidUser(string $user_id) : bool
    {
        $user_service = new AdminUserService();
        $admin = $user_service->getUser($user_id);
        if (!$admin->id) {
            return false;
        }

        return $admin && $admin->is_use;
    }

    public function getAdminAuth(?string $user_id = null) : array
    {
        if (empty($user_id)) {
            $user_id = LoginService::GetAdminID();
        }

        $user_service = new AdminUserService();
        $menu_auths = $user_service->getAllMenus($user_id);
        $ajax_auths = $user_service->getAllMenuAjaxList($user_id);
        $auths = array_merge($menu_auths, $ajax_auths);

        return $auths;
    }

    private static function parseUrlAuth(string $url) : array
    {
        $tokens = preg_split('/#/', $url);
        return [
            'url' => $tokens[0] ?? null,
            'hash' => $tokens[1] ?? null,
        ];
    }

    // 입력받은 url이 권한을 가지고 있는 url인지 검사<br/>
    // '/comm/'으로 시작하는 url은 권한을 타지 않는다. 
    // (개인정보 수정 등 로그인 한 유저가 공통적으로 사용할 수 있는 기능을 /comm/에 넣을 예정)
    private static function isAuthUrl(string $check_url, string $menu_url) : bool
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

    private static function isAuthCorrect($hash, $auth) : bool
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

    public static function isWhiteListUrl(string $check_url) : bool
    {
        $public_urls = [
            '/admin/book/pa',
            '/me', // 본인 정보 수정
            '/welcome',
            '/logout',
            '/login-azure',
            '/token-introspect',
            '/index.php',
            '/',
        ];

        return in_array($check_url, $public_urls);
    }

    public static function readUserAuth(string $user_id) : array
    {
        $user_service = new AdminUserService();
        $menu_urls = $user_service->getAllMenus($user_id, 'menu_url');
        $ajax_urls = $user_service->getAllMenuAjaxList($user_id, 'ajax_url');
        $urls = array_merge($menu_urls, $ajax_urls);

        return $urls;
    }

    public static function checkAuth(?string $hash, string $check_url, array $auth_list) : bool
    {
        if (self::isWhiteListUrl($check_url)) {
            return true;
        }

        foreach ($auth_list as $auth) {
            $auth = self::parseUrlAuth($auth);
            if (self::isAuthUrl($check_url, $auth['url'])
                && self::isAuthCorrect($hash, $auth['hash'] ?? [])) {
                return true;
            }
        }
        return false;
    }

    // 해당 URL의 Hash 권한이 있는지 검사한다.
    public static function hasHashAuth(?string $hash, string $check_url, ?string $admin_id = null) : bool
    {
        $admin_id = $admin_id ?? LoginService::GetAdminID();
        if (!self::isValidUser($admin_id)) {
            return false;
        }

        $auth_list = self::readUserAuth($admin_id);

        return self::checkAuth($hash, $check_url, $auth_list);
    }

    // 해당 URL의 Hash 권한 Array를 반환한다.
    public static function getCurrentHashArray(string $check_url = null, string $admin_id = null) : array
    {
        if (!isset($check_url) || trim($check_url) === '') {
            $check_url = $_SERVER['REQUEST_URI'];
        }

        $auths = self::readUserAuth($admin_id ?? LoginService::GetAdminID());
        $hash_array = self::getHashesFromMenus($check_url, $auths);

        return $hash_array;
    }

    public static function getHashesFromMenus(string $check_url, array $auth_urls) : array
    {
        $auth_urls = array_filter($auth_urls, function ($url) use ($check_url) {
            return self::isAuthUrl($check_url, $url);
        });

        $hash_array = array_map(function ($url) {
            return self::parseUrlAuth($url)['hash'];
        }, $auth_urls);

        return $hash_array;
    }

    public function hideEmptyRootMenus(array $menus) : array
    {
        $topMenuFlags = array_map(function ($menu) {
            $url = self::parseUrlAuth($menu['menu_url'])['url'];
            return $menu['menu_deep'] == 0 && strlen($url) == 0;
        }, $menus);

        $topMenuFlags[] = true; // For tail check
        for ($i = 0; $i < count($menus); ++$i) {
            if ($topMenuFlags[$i] && $topMenuFlags[$i + 1]) {
                $menus[$i]['is_show'] = false;
            }
        }

        return $menus;
    }
}
