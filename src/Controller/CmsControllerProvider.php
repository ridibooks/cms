<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\Lib\MiddlewareFactory;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class CmsControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $login = new LoginController();
        $controllers->get('/login', [$login, 'getLoginPage']);
        $controllers->get('/login-azure', [$login, 'azureLogin']);
        $controllers->get('/logout', [$login, 'logout']);
        $controllers->post('/token-introspect', [$login, 'tokenIntrospect']);
        $controllers->match('/token-refresh', [$login, 'tokenRefresh']);

        $common = new CommonController();
        $controllers->get('/', [$common, 'index']);
        $controllers->get('/welcome', [$common, 'getWelcomePage'])
            ->before(MiddlewareFactory::authRequired());
        $controllers->get('/comm/user_list.ajax', [$common, 'userList'])
            ->before(MiddlewareFactory::authRequired());
        $controllers->get('/me', [$common, 'getMyInfo'])
            ->before(MiddlewareFactory::authRequired());
        $controllers->post('/me', [$common, 'updateMyInfo'])
            ->before(MiddlewareFactory::authRequired());

        return $controllers;
    }
}
