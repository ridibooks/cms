<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Authenticator;

use Symfony\Component\HttpFoundation\Request;

interface AuthenticatorInterface
{
    public function getAuthType(): ?string;

    public function setAuthType(string $auth);

    public function createCredential(Request $request);

    public function validateCredential($credentials);

    public function removeCredential();

    public function getUserId($credentials): string;
}
