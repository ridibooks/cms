<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service\Auth\Controller;

use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class OAuth2Controller
{
    private $authenticator;
    private $default_home_url;

    public function __construct(OAuth2Authenticator $authenticator, string $default_home_url)
    {
        $this->authenticator = $authenticator;
        $this->default_home_url = $default_home_url;
    }

    public function authorize(Request $request, string $provider)
    {
        $return_url = $request->get('return_url', $this->default_home_url);
        $scope = $request->get('scope');

        $this->authenticator->setAuthType('oauth2');
        $this->authenticator->setProvider($provider);
        $this->authenticator->setReturnUrl($return_url);
        $authorization_url = $this->authenticator->getAuthorizationUrl($scope);
        return new RedirectResponse($authorization_url);
    }

    public function callback(Request $request)
    {
        $access_token = $this->authenticator->createCredential($request);
        $this->authenticator->validateCredential($access_token);

        $return_url = $this->authenticator->getReturnUrl($this->default_home_url);
        $this->authenticator->setReturnUrl(null);
        return new RedirectResponse($return_url);
    }

//    public function revoke()
//    {
//        $this->authenticator->removeCredential();
//        return new RedirectResponse($this->default_home_url);
//    }
}
