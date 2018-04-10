<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Exception;

class InvalidCredentialException extends AuthenticationException
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
