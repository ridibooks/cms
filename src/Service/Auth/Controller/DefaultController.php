<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Controller;

use Ridibooks\Cms\Service\Auth\Authenticator\AuthenticatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController
{
    private $authenticator;
    private $default_return_url;

    public function __construct(AuthenticatorInterface $authenticator, $default_return_url)
    {
        $this->authenticator = $authenticator;
        $this->default_return_url = $default_return_url;
    }

    public function authorize(Request $request)
    {
        $this->authenticator->setAuthType('password');

        $credential = $this->authenticator->createCredential($request);
        $this->authenticator->validateCredential($credential);

        $return_url = $request->get('return_url', $this->default_return_url);
        return new RedirectResponse($return_url);
    }
}
