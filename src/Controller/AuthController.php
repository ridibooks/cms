<?php

namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\Service\Auth\Authenticator\AuthCookieStorage;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController
{
    public function loginPage(Request $request, Application $app)
    {
        $home_url = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $home_url);

        $azure_authorize_url = $app['url_generator']->generate('oauth2_authorize', [
            'provider' => 'azure',
        ]);

        return $app->render('login.twig', [
            'azure_authorize_url' => $azure_authorize_url . '?return_url=' . $return_url,
        ]);
    }

    public function logout(Request $request, Application $app)
    {
        $login_url = $app['url_generator']->generate('login');

        /** @var AuthCookieStorage $storage */
        $storage = $app['auth.storage'];
        $storage->clearAll();

        $return_url = $request->get('return_url', $login_url);

        return new RedirectResponse($return_url);
    }
}
