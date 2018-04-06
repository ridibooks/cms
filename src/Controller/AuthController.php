<?php

namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Service\LoginService;
use Ridibooks\Cms\Util\UrlHelper;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController
{
    const RETURN_URL_COOKIE_NAME = 'return_url';

    public function login(Request $request, Application $app)
    {
        $return_url = $request->get('return_url', $app['url_generator']->generate('home'));

        $response = Response::create();
        $response->headers->setCookie(new Cookie(self::RETURN_URL_COOKIE_NAME, $return_url));

        return $app->render('login.twig', [
            'azure_login' => $this->buildAuthenticationEndpoint($app),
        ], $response);
    }

    public function logout(Application $app)
    {
        return LoginService::handleLogout($app['url_generator']->generate('login'));
    }

    private function buildAuthenticationEndpoint(Application $app)
    {
        if (!empty($app['test_id'])) {
            $end_point = $app['url_generator']->generate('azureCallback') . '?code=test';
        } else {
            /** @var AzureOAuth2Service $azure */
            $azure = $app['azure'];
            $end_point = $azure->getAuthenticationEndPoint();
        }
        return $end_point;
    }

    public function authorize(Request $request, Application $app)
    {
        $login_path = $app['url_generator']->generate('login');
        $home_path = $app['url_generator']->generate('home');
        $return_url = $request->get('return_url', $home_path);
        return LoginService::handleAuthorize($return_url, $login_path, $app['azure'], $app['logger']);
    }

    public function azureCallback(Request $request, Application $app)
    {
        $code = $request->get('code');
        $return_url = $request->cookies->get(self::RETURN_URL_COOKIE_NAME, $app['url_generator']->generate('home'));

        try {
            if (!empty($app['test_id'])) {
                $response = LoginService::handleTestLogin($return_url, $app['test_id']);
            } else {
                /** @var AzureOAuth2Service $azure */
                $azure = $app['azure'];
                $response = LoginService::handleAzureLogin($return_url, $code, $azure);
            }
        } catch (\Exception $e) {
            return UrlHelper::printAlertRedirect($return_url, $e->getMessage());
        }

        $response->headers->clearCookie(self::RETURN_URL_COOKIE_NAME);
        return $response;
    }

    /** @deprecated  */
    public function tokenIntrospect(Request $request, Application $app)
    {
        $token = $request->get('token');
        if (empty($token)) {
            return Response::create('Bad parameters', Response::HTTP_BAD_REQUEST);
        }
        if (!empty($app['test_id'])) {
            $token_resource = [
                'user_id' => $app['test_id'],
                'user_name' => 'test',
            ];
        } else {
            $azure = new AzureOAuth2Service($app['azure.options']);
            $token_resource = $azure->introspectToken($token);
        }
        return JsonResponse::create($token_resource,
            isset($token_resource['error']) ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }

    /** @deprecated  */
    public function tokenRefresh(Request $request, Application $app)
    {
        $return_url = $request->get('return_url', '/welcome');
        $refresh_token = $request->cookies->get(LoginService::REFRESH_COOKIE_NAME);
        if (empty($refresh_token)) {
            return RedirectResponse::create("/login?return_url=$return_url");
        }
        $azure = new AzureOAuth2Service($app['azure.options']);
        return LoginService::refreshToken($return_url, $refresh_token, $azure);
    }
}
