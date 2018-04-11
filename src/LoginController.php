<?php

namespace Ridibooks\Cms;

use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\Lib\AzureOAuth2Service;
use Ridibooks\Cms\Service\LoginService;
use Ridibooks\Platform\Cms\Auth\AdminAuthService;
use Ridibooks\Platform\Cms\Util\UrlHelper;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
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
        $controller_collection->get('/login-azure', [$this, 'loginWithAzure']);

        // login page
        $controller_collection->get('/authorize', [$this, 'authorize']);

        // logout
        $controller_collection->get('/logout', [$this, 'logout']);

        return $controller_collection;
    }

    private function getAzureAuthorizeEndpoint(CmsServerApplication $app)
    {
        if (!empty($app['test_id'])) {
            $end_point = '/login-azure?code=test';
        } else {
            $azure_config = $app['azure'];
            $end_point = AzureOAuth2Service::getAuthorizeEndPoint($azure_config);
        }

        return $end_point;
    }

    public function getLoginPage(Request $request, CmsServerApplication $app)
    {
        $end_point = $this->getAzureAuthorizeEndpoint($app);

        $response = Response::create();
        $return_url = $request->get('return_url', '/welcome');
        $response->headers->setCookie(new Cookie('return_url', $return_url));

        return $app->render('login.twig', [
            'azure_login' => $end_point
        ], $response);
    }

    public function loginWithAzure(Request $request, Application $app)
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

        try {
            if (!empty($app['test_id'])) {
                LoginService::setSessions($app['test_id']);
                $token = 'test';
                $refresh = '';
                $admin_id = $app['test_id'];
            } else {
                $tokens = AzureOAuth2Service::getAccessToken($code, $app['azure']);
                $token = $tokens['token'];
                $refresh = $tokens['refresh'];
                $resource = AzureOAuth2Service::getTokenResource($token, $app['azure']);
                LoginService::doLoginWithAzure($resource);
                $admin_id = $resource['user_id'];
            }
        } catch (\Exception $e) {
            return UrlHelper::printAlertRedirect($return_url, $e->getMessage());
        }

        $response = RedirectResponse::create($return_url);
        $response->headers->clearCookie('return_url');
        $response->headers->setCookie(new Cookie(
            'cms-token', $token, time() + (30 * 24 * 60 * 60), '/', null, !$app['debug']
        ));
        $response->headers->setCookie(new Cookie(
            'cms-refresh', $refresh, time() + (30 * 24 * 60 * 60), '/', null, !$app['debug']
        ));
        $response->headers->setCookie(new Cookie(
            'admin-id', $admin_id, time() + (30 * 24 * 60 * 60), '/', null, !$app['debug']
        ));
        return $response;
    }

    public function authorize(Request $request, CmsServerApplication $app)
    {
        $end_point = $this->getAzureAuthorizeEndpoint($app);
        $return_url = $request->get('return_url', '/welcome');

        $response = RedirectResponse::create($end_point);
        $response->headers->setCookie(new Cookie('return_url', $return_url));

        return $response;
    }

    public function logout(CmsServerApplication $app)
    {
        LoginService::resetSession();
        $response = RedirectResponse::create('/login');
        $response->headers->clearCookie('admin-id', '/', null, !$app['debug']);
        $response->headers->clearCookie('cms-token', '/', null, !$app['debug']);
        $response->headers->clearCookie('cms-refresh', '/', null, !$app['debug']);
        return $response;
    }
}
