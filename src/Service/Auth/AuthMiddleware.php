<?php

namespace Ridibooks\Cms\Service\Auth;

use Ridibooks\Cms\Service\Auth\Authenticator\AuthCookieStorage;
use Ridibooks\Cms\Service\Auth\Authenticator\AuthenticatorInterface;
use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthMiddleware
{
    public static function authRequired(): callable
    {
        return function (Request $request, Application $app) {
            try {
                /** @var AuthCookieStorage $storage */
                $storage = $app['auth.storage'];
                $auth_type = $storage->get(BaseAuthenticator::KEY_AUTH);
                if (empty($auth_type)) {
                    throw new Exception\NoCredentialException();
                }

                /** @var AuthenticatorInterface $authenticator */
                $authenticator = $app['auth.' . $auth_type . '.authenticator'];

                $credentials = $authenticator->createCredential($request);
                $authenticator->validateCredential($credentials);

                $user_id = $authenticator->getUserId($credentials);
                if (empty($user_id)) {
                    throw new Exception\NoCredentialException();
                }
            } catch (\Exception $e) {
                $login_url = $app['url_generator']->generate('login');
                $return_url = $request->getRequestUri();
                return new RedirectResponse($login_url . '?return_url=' . urlencode($return_url));
            }

            $request->attributes->set('user_id', $user_id);
        };
    }
}
