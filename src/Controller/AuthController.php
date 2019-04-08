<?php

namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\Service\AdminUserService;
use Ridibooks\Cms\Service\Auth\Authenticator\BaseAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\OAuth2Authenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\PasswordAuthenticator;
use Ridibooks\Cms\Service\Auth\Authenticator\TestAuthenticator;
use Ridibooks\Cms\Service\Auth\Exception\NoCredentialException;
use Ridibooks\Cms\Service\Auth\OAuth2\Client\AzureClient;
use Ridibooks\Cms\Service\Auth\OAuth2\Exception\InvalidStateException;
use Ridibooks\Cms\Service\Auth\OAuth2\Exception\OAuth2Exception;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController
{
    public function loginPage(Request $request, Application $app)
    {
        $return_url = $request->get('return_url');
        $authorize_urls = $this->createAuthorizeUrls($app['auth.enabled'], $app['url_generator'], $return_url);

        return $app['twig']->render('login.twig', $authorize_urls);
    }

    private function createAuthorizeUrls(array $auth_enabled, UrlGeneratorInterface $url_generator, ?string $return_url): array
    {
        if (empty($return_url)) {
            $return_url = $url_generator->generate('home');
        }

        $twig_params = [];
        if (in_array(OAuth2Authenticator::AUTH_TYPE, $auth_enabled)) {
            $azure_authorize_url = $url_generator->generate('oauth2_code', [
                'provider' => AzureClient::PROVIDER_NAME,
            ]);
            $azure_authorize_url .= '?return_url=' . urlencode($return_url);
            $twig_params['azure_authorize_url'] = $azure_authorize_url;
        }

        if (in_array(PasswordAuthenticator::AUTH_TYPE, $auth_enabled)) {
            $password_authorize_url = $url_generator->generate('default_authorize', [
                'auth_type' => PasswordAuthenticator::AUTH_TYPE,
            ]);
            $password_authorize_url .= '?return_url=' . urlencode($return_url);
            $twig_params['password_authorize_url'] = $password_authorize_url;
        }

        if (in_array(TestAuthenticator::AUTH_TYPE, $auth_enabled)) {
            $test_authorize_url = $url_generator->generate('default_authorize', [
                'auth_type' => TestAuthenticator::AUTH_TYPE,
            ]);
            $test_authorize_url .= '?return_url=' . urlencode($return_url);
            $twig_params['test_authorize_url'] = $test_authorize_url;
        }

        return $twig_params;
    }

    public function logout(Request $request, Application $app)
    {
        $login_url = $app['url_generator']->generate('login');

        /** @var BaseAuthenticator $auth */
        $auth = $app['auth.authenticator'];
        if (isset($auth)) {
            $auth->signOut();
        }

        $return_url = $request->get('return_url', $login_url);

        return new RedirectResponse($return_url);
    }

    public function authorize(Request $request, Application $app, string $auth_type)
    {
        /** @var BaseAuthenticator $auth */
        $auth = $app['auth.authenticator.' . $auth_type];
        $auth->signIn($request);

        // TODO: When it comes to fail?

        $home_url = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $home_url);

        return new RedirectResponse($return_url);
    }

    public function getAuthorizationCode(Request $request, Application $app, ?string $provider)
    {
        $home_url = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $home_url);
        $scope = $request->get('scope');

        /** @var OAuth2Authenticator $auth */
        $auth = $app['auth.authenticator.oauth2'];
        $auth->setProvider($provider);
        $auth->setReturnUrl($return_url);

        $authorization_url = $auth->getAuthorizationUrl($scope);

        return new RedirectResponse($authorization_url);
    }

    public function getToken(Request $request, Application $app)
    {
        /** @var OAuth2Authenticator $auth */
        $auth = $app['auth.authenticator.oauth2'];

        try {
            $auth->createCredential($request);
        } catch (NoCredentialException | InvalidStateException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'message' => 'success'
        ], Response::HTTP_OK);
    }

    public function authorizeWithOAuth2(Request $request, Application $app)
    {
        /** @var OAuth2Authenticator $auth */
        $auth = $app['auth.authenticator.oauth2'];

        $home_url = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $auth->getReturnUrl() ?? $home_url);
        $auth->setReturnUrl(null);

        try {
            $user = $auth->signIn($request);
        } catch (NoCredentialException $e) {
            $login_url = $app['url_generator']->generate('login') . '?return_url=' . urlencode($return_url);

            return new RedirectResponse($login_url);
        } catch (InvalidStateException | OAuth2Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $user_service = new AdminUserService();
        $user = $this->removePersonalInfo($user, $app);
        $user_service->updateOrCreateUser($user);

        return new RedirectResponse($return_url);
    }

    private function removePersonalInfo(array $user, Application $app)
    {
        // Remove personal information in non-production.
        if ($app['debug']) {
            unset($user['name']);
            unset($user['team']);
            unset($user['email']);
        }
        return $user;
    }
}
