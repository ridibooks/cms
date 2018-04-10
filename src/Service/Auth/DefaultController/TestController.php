<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\DefaultController;

use Ridibooks\Cms\Service\Auth\Authenticator\AuthenticatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class TestController
{
    private $authenticator;
    private $default_home_url;

    public function __construct(AuthenticatorInterface $authenticator, string $default_home_url)
    {
        $this->authenticator = $authenticator;
        $this->default_home_url = $default_home_url;
    }

    public function authorize(Request $request)
    {
        $this->authenticator->setAuthType('test');

        $return_url = $request->get('return_url', $this->default_home_url);
        return new RedirectResponse($return_url);
    }

//    public function logout(Request $request)
//    {
//        $this->authenticator->removeCredential();
//
//        $return_url = $request->get('return_url', $this->default_home_url);
//        return new RedirectResponse($return_url);
//    }
}
