<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use PHPUnit\Framework\TestCase;
use Ridibooks\Cms\Service\AdminAuthService;
use Ridibooks\Cms\Service\AdminUserService;

class AdminAuthServiceTest extends TestCase
{
    public function testAuthorizeRequest()
    {
        $this->assertTrue(
            AdminAuthService::authorizeRequest('devgrapher', '/super/logs')
        );

        $this->assertFalse(
            AdminAuthService::authorizeRequest('devgrapher', '/super/false')
        );

        // ajax test
        $this->assertTrue(
            AdminAuthService::authorizeRequest('devgrapher', '/super/tag_action.ajax')
        );
        $this->assertFalse(
            AdminAuthService::authorizeRequest('devgrapher', '/super/false.ajax')
        );
    }
}
