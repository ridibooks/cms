<?php

namespace Ridibooks\Cms\Service;

use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Thrift\Errors\ErrorCode;
use Ridibooks\Cms\Thrift\Errors\MalformedTokenException;
use Ridibooks\Cms\Thrift\Errors\NoTokenException;
use Ridibooks\Cms\Thrift\Errors\UnauthorizedException;

/**권한 설정 Service
 * @deprecated
 */
class AdminAuthService
{
    public function getAdminMenu(string $user_id): array
    {
        if (!empty($_ENV['TEST_AUTH_DISABLE'])) {
            $menu_service = new AdminMenuService();
            $menus = $menu_service->queryMenus(true);
        } else {
            $user_service = new AdminUserService();
            $menus = $user_service->getAllMenus($user_id);
        }

        $menus = $this->hideEmptyRootMenus($menus);

        $admin_menus = [];
        foreach ($menus as $menu) {
            if ($menu['is_use'] == 1 && $menu['is_show'] == 1) {
                $admin_menus[$menu['id']] = $menu;
            }
        }

        return $admin_menus;
    }

    public function hideEmptyRootMenus(array $menus): array
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

    /**
     * @throws NoTokenException
     * @throws MalformedTokenException
     * @throws UnauthorizedException
     */
    public function authorize(string $token, array $methods, string $check_url)
    {
        if (empty($token)) {
            throw new NoTokenException([
                'code' => ErrorCode::BAD_REQUEST,
                'message' => '토큰을 찾을 수 없습니다.',
            ]);
        }

        /** @var AzureOAuth2Service $azure */
        $azure = new AzureOAuth2Service([
            'tenent' => $_ENV['AZURE_TENENT'] ?? '',
            'client_id' => $_ENV['AZURE_CLIENT_ID'] ?? '',
            'client_secret' => $_ENV['AZURE_CLIENT_SECRET'] ?? '',
            'resource' => $_ENV['AZURE_RESOURCE'] ?? '',
            'redirect_uri' => $_ENV['AZURE_REDIRECT_URI'] ?? '',
            'api_version' => $_ENV['AZURE_API_VERSION'] ?? '',
        ]);

        $token_resource = $azure->introspectToken($token);
        if (isset($token_resource['error'])) {
            throw new MalformedTokenException([
                'code' => ErrorCode::BAD_REQUEST,
                'message' => '잘못된 토큰입니다.',
            ]);
        }

        if (!empty($_ENV['TEST_AUTH_DISABLE'])) {
            return;
        }

        if (!self::checkAuth($methods, $check_url, $token_resource['user_id'])) {
            throw new UnauthorizedException([
                'code' => ErrorCode::BAD_REQUEST,
                'message' => '접근 권한이 없습니다.',
            ]);
        }
    }

    public function checkAuth(array $check_method, string $check_url, string $admin_id): bool
    {
        $parsed = parse_url($check_url);
        $check_url = $parsed['path'];

        if (!$this->isValidUser($admin_id)) {
            return false;
        }

        if ($this->isWhiteListUrl($check_url)) {
            return true;
        }

        $auth_list = $this->readUserAuth($admin_id);
        foreach ($auth_list as $auth) {
            if ($this->hasAuthority($check_url, $auth)) {
                return true;
            }
        }

        return false;
    }

    private function hasAuthority($check_url, $menu_url)
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

    private function isWhiteListUrl(string $check_url): bool
    {
        $public_urls = [
            '/admin/book/pa',
            '/me', // 본인 정보 수정
            '/welcome',
            '/',
        ];

        return in_array($check_url, $public_urls);
    }

    private function isValidUser(string $user_id): bool
    {
        if (!$user_id) {
            return false;
        }

        $user_service = new AdminUserService();
        $admin = $user_service->getUser($user_id);
        if (!$admin->id) {
            return false;
        }

        return $admin && $admin->is_use;
    }

    private function readUserAuth(string $user_id): array
    {
        $user_service = new AdminUserService();
        $menu_urls = $user_service->getAllMenus($user_id, 'menu_url');
        $ajax_urls = $user_service->getAllMenuAjaxList($user_id, 'ajax_url');
        $urls = array_merge($menu_urls, $ajax_urls);

        return $urls;
    }

    // 해당 URL의 Hash 권한이 있는지 검사한다.

    /** @deprecated */
    public function hasHashAuth(?string $hash, string $check_url, string $admin_id): bool
    {
        if (!empty($_ENV['TEST_AUTH_DISABLE'])) {
            return true;
        }

        if (!$this->isValidUser($admin_id)) {
            return false;
        }
        
        if ($this->isWhiteListUrl($check_url)) {
            return true;
        }

        $auth_list = self::readUserAuth($admin_id);

        foreach ($auth_list as $auth) {
            $auth = $this->parseUrlAuth($auth);
            if ($this->compareUrl($check_url, $auth['url'])
                && $this->compareHash($hash, $auth['hash'] ?? [])) {
                return true;
            }
        }

        return false;
    }

    private function parseUrlAuth(string $url): array
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
    private function compareUrl(string $check_url, string $menu_url): bool
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

    private function compareHash($check_hash, $auth): bool
    {
        if (is_null($check_hash)) { //hash가 없는 경우 (보기 권한)
            return true;
        } elseif (is_array($check_hash)) { //hash가 array인 경우
            foreach ($check_hash as $h) {
                if (in_array($h, $auth)) {
                    return true;
                }
            }
        } elseif (is_array($auth) && in_array($check_hash, $auth)) {
            return true;
        } elseif ($auth == $check_hash) {
            return true;
        }
        return false;
    }

    // 해당 URL의 Hash 권한 Array를 반환한다.
    public function getCurrentHashArray(string $check_url, string $admin_id): array
    {
        if (!isset($check_url) || trim($check_url) === '') {
            $check_url = $_SERVER['REQUEST_URI'];
        }

        $auths = $this->readUserAuth($admin_id);
        $hash_array = $this->getHashesFromMenus($check_url, $auths);

        return $hash_array;
    }

    public function getHashesFromMenus(string $check_url, array $auth_urls): array
    {
        $auth_urls = array_filter($auth_urls, function ($url) use ($check_url) {
            return $this->compareUrl($check_url, $url);
        });

        $hash_array = array_map(function ($url) {
            return $this->parseUrlAuth($url)['hash'];
        }, $auth_urls);

        return array_filter($hash_array);
    }
}
