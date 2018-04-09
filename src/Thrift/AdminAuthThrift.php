<?php

namespace Ridibooks\Cms\Thrift;

use Ridibooks\Cms\Service\AdminAuthService;
use Ridibooks\Cms\Thrift\AdminAuth\AdminAuthServiceIf;
use Ridibooks\Cms\Thrift\AdminAuth\AdminMenu;
use Ridibooks\Cms\Thrift\Errors\ErrorCode;
use Ridibooks\Cms\Thrift\Errors\MalformedTokenException;
use Ridibooks\Cms\Thrift\Errors\NoTokenException;
use Ridibooks\Cms\Thrift\Errors\SystemException;
use Ridibooks\Cms\Thrift\Errors\UnauthorizedException;

class AdminAuthThrift implements AdminAuthServiceIf
{
    /** @var AdminAuthService $server */
    private $server = null;

    public function __construct(AdminAuthService $server)
    {
        $this->server = $server;
    }

    /** @throws SystemException */
    public function hasUrlAuth($hash, $checkUrl, $adminId)
    {
        throw new SystemException([
            'code' => ErrorCode::INTERNAL_SERVER_ERROR,
            'message' => 'hasUrlAuth is not used anymore'
        ]);
    }

    public function hasHashAuth($hash, $checkUrl, $adminId)
    {
        return $this->server->hasHashAuth($hash, $checkUrl, $adminId);
    }

    public function getCurrentHashArray($checkUrl, $adminId)
    {
        return $this->server->getCurrentHashArray($checkUrl, $adminId);
    }

    public function getAdminMenu($adminId)
    {
        $menus = $this->server->getAdminMenu($adminId);
        return array_map(function ($menu) {
            return new AdminMenu($menu);
        }, $menus);
    }

    /**
     * @throws NoTokenException
     * @throws MalformedTokenException
     * @throws UnauthorizedException
     */
    public function authorize($token, array $methods, $check_url)
    {
        $this->server->authorize($token, $methods, $check_url);
    }
}
