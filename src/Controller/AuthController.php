<?php

namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
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

        /** @var BaseAuthenticator $auth */
        $auth = $app['auth.authenticator'];
        $auth->signOut();

        $return_url = $request->get('return_url', $login_url);

        return new RedirectResponse($return_url);
    }

    public function authorize(Request $request, Application $app, string $auth_type)
    {
        /** @var BaseAuthenticator $auth */
        $auth = $app['auth.' . $auth_type . '.authenticator'];
        $auth->signIn($request);

        $home_url = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $home_url);
        return new RedirectResponse($return_url);
    }

    public function authorizeWithOAuth2(Request $request, Application $app, string $provider)
    {
        $home_url = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $home_url);
        $scope = $request->get('scope');

        /** @var OAuth2Authenticator $auth */
        $auth = $app['auth.oauth2.authenticator'];
        $auth->setProvider($provider);
        $auth->setReturnUrl($return_url);

        $authorization_url = $auth->getAuthorizationUrl($scope);
        return new RedirectResponse($authorization_url);
    }

    public function callbackFromOAuth2(Request $request, Application $app)
    {
        /** @var OAuth2Authenticator $auth */
        $auth = $app['auth.oauth2.authenticator'];

        $user_id = $auth->signIn($request);

        // TODO: add an user model if not exists.

        $home_url = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $home_url);
        return new RedirectResponse($return_url);
    }
}
