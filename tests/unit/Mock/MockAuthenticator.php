<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Tests\Mock;

use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Ridibooks\Cms\Service\Auth\Exception\InvalidCredentialException;
use Symfony\Component\HttpFoundation\Request;

class MockAuthenticator extends BaseAuthenticator
{
    private $allows_validation = false;

    public function __construct(bool $allows_validation)
    {
        parent::__construct('mock', new MockSession());
        $this->allows_validation = $allows_validation;
    }

    public function createCredential(Request $request)
    {
        return 'mock';
    }

    /** @throws InvalidCredentialException */
    public function validateCredential($mock)
    {
        if (!$this->allows_validation) {
            throw new InvalidCredentialException('validateCredential failed');
        }
    }

    public function removeCredential()
    {

    }

    public function getUserId($mock): string
    {
        return $mock;
    }
}
