<?php

namespace Ridibooks\Cms\Service\Auth;

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
                /** @var BaseAuthenticator $authenticator */
                $authenticator = $app['auth.authenticator'];
                if (empty($authenticator)) {
                    throw new Exception\NoCredentialException();
                }

                $user_id = $authenticator->signIn($request);
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