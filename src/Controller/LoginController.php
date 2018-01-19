<?php

namespace Ridibooks\Cms\Controller;

use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Service\LoginService;
use Ridibooks\Cms\Util\UrlHelper;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];

        // login page
        $controller_collection->get('/login', [$this, 'getLoginPage']);

        // login process
        $controller_collection->get('/login-azure', [$this, 'azureLogin']);

        // logout
        $controller_collection->get('/logout', [$this, 'logout']);

        $controller_collection->post('/token-introspect', [$this, 'tokenIntrospect']);

        return $controller_collection;
    }

    public function getLoginPage(Request $request, CmsApplication $app)
    {
        if (!empty($app['test_id'])) {
            $end_point = '/login-azure?code=test';
        } else {
            $azure_config = $app['azure'];
            $end_point = AzureOAuth2Service::getAuthorizeEndPoint($azure_config);
        }
        $return_url = $request->get('return_url', '/welcome');

        $response = Response::create();
        $response->headers->setCookie(new Cookie('return_url', $return_url));

        return $app->render('login.twig', [
            'azure_login' => $end_point
        ], $response);
    }

    public function azureLogin(Request $request, Application $app)
    {
        $code = $request->get('code');
        $return_url = $request->cookies->get('return_url', '/welcome');

        if (!$code) {
            $error = $request->get('error');
            $error_description = $request->get('error_description');

            $sentry_client = $app[SentryServiceProvider::SENTRY];
            if ($sentry_client) {
                $sentry_client->captureMessage($error_description, [
                    'extra' => ['error_code' => $error]
                ]);
            }

            return Response::create('azure login fail', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = RedirectResponse::create($return_url);
        $response->headers->clearCookie('return_url');

        try {
            if (!empty($app['test_id'])) {
                $response = LoginService::handleTestLogin($response, $app['test_id']);
            } else {
                $response = LoginService::handleAzureLogin($response, $code, $app['azure']);
            }
        } catch (\Exception $e) {
            return UrlHelper::printAlertRedirect($return_url, $e->getMessage());
        }
        return $response;
    }

    public function logout()
    {
        $response = RedirectResponse::create('/login');
        $response = LoginService::clearLoginCookies($response);
        return $response;
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
                'user_name' => 'test' ,
            ];
        } else {
            $token_resource = AzureOAuth2Service::introspectToken($token, $app['azure']);
        }
        return JsonResponse::create($token_resource);
    }
}
