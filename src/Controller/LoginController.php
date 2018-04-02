<?php

namespace Ridibooks\Cms\Controller;

use Moriony\Silex\Provider\SentryServiceProvider;
use Raven_Client;
use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Service\LoginService;
use Ridibooks\Cms\Util\UrlHelper;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController
{
    public function getLoginPage(Request $request, CmsApplication $app)
    {
        $end_point = $this->buildAuthorizeEndpoint($request, $app);

        $response = Response::create();
        $return_url = $request->get('return_url', '/welcome');
        $response->headers->setCookie(new Cookie('return_url', $return_url));

        return $app->render('login.twig', [
            'azure_login' => $end_point
        ], $response);
    }

    private function buildAuthorizeEndpoint(Request $request, Application $app)
    {
        if (!empty($app['test_id'])) {
            $end_point = '/login-azure?code=test';
        } else {
            $azure = new AzureOAuth2Service($app['azure']);
            $end_point = $azure->getAuthorizeEndPoint();
        }
        return $end_point;
    }

    public function azureLogin(Request $request, Application $app)
    {
        $code = $request->get('code');
        $return_url = $request->cookies->get('return_url', '/welcome');

        if (!$code) {
            $error = $request->get('error');
            $error_description = $request->get('error_description');

            /** @var Raven_Client $sentry_client */
            $sentry_client = $app[SentryServiceProvider::SENTRY];
            if ($sentry_client) {
                $sentry_client->captureMessage($error_description, [
                    'extra' => ['error_code' => $error]
                ]);
            }

            return Response::create('azure login fail', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            if (!empty($app['test_id'])) {
                $response = LoginService::handleTestLogin($return_url, $app['test_id']);
            } else {
                $azure = new AzureOAuth2Service($app['azure']);
                $response = LoginService::handleAzureLogin($return_url, $code, $azure);
            }
        } catch (\Exception $e) {
            return UrlHelper::printAlertRedirect($return_url, $e->getMessage());
        }

        $response->headers->clearCookie('return_url');
        return $response;
    }

    public function logout(Request $request, Application $app)
    {
        $redirect_url = $request->getUriForPath('/login');
        if (!empty($app['test_id'])) {
            $endpoint = $redirect_url;
        } else {
            $azure = new AzureOAuth2Service($app['azure']);
            $endpoint = $azure->getLogoutEndpoint($redirect_url);
        }

        return LoginService::handleLogout($endpoint);
    }

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
            $azure = new AzureOAuth2Service($app['azure']);
            $token_resource = $azure->introspectToken($token);
        }
        return JsonResponse::create($token_resource,
            isset($token_resource['error']) ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }

    public function tokenRefresh(Request $request, Application $app)
    {
        $return_url = $request->get('return_url', '/welcome');
        $refresh_token = $request->cookies->get(LoginService::REFRESH_COOKIE_NAME);

        if (empty($refresh_token)) {
            return RedirectResponse::create("/login?return_url=$return_url");
        }

        $azure = new AzureOAuth2Service($app['azure']);
        return LoginService::refreshToken($return_url, $refresh_token, $azure);
    }
}
