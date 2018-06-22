<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth;

class CookieSessionPolicyForAuthSubdomain
{
    const SERVICE_DOMAIN_EXPIRES_SEC = 60 * 60 * 2; // 2 hours
    const AUTH_DOMAIN_EXPIRES_SEC = 60 * 60 * 24 * 30; // 30 days
    const AUTH_SUBDOMAIN = 'auth.';

    private $auth_domain;
    private $service_domain;

    public function __construct(string $server_host)
    {
        $this->auth_domain = $server_host;
        $this->service_domain = str_replace(self::AUTH_SUBDOMAIN, '', $this->auth_domain);
    }

    public function getCookieOptions()
    {
        return [
            'service' => $this->getServiceDomainCookieOptions(),
            'auth' => $this->getAuthDomainCookieOptions(),
        ];
    }

    private function getServiceDomainCookieOptions()
    {
        return [
            'domain' => $this->service_domain,
            'path' => '/',
            'expires_on' => time() + self::SERVICE_DOMAIN_EXPIRES_SEC,
            'secure' => false,
        ];
    }

    private function getAuthDomainCookieOptions()
    {
        return [
            'domain' => null, // Use current domain.
            'path' => '/',
            'expires_on' => time() + self::AUTH_DOMAIN_EXPIRES_SEC,
            'secure' => empty($_ENV['TEST_SECURED_DISABLE']) ? true : false,
        ];
    }
}