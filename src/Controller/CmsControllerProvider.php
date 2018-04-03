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

        // Thrift service
        $thrift = new ThriftController();
        $app->post('/', [$thrift, 'process'])
            ->before(MiddlewareFactory::thriftContent())
            ->bind('thrift');

        // Login service
        $auth = new AuthController();
        $controllers->get('/login', [$auth, 'login'])
            ->bind('login');
        $controllers->get('/logout', [$auth, 'logout'])
            ->bind('logout');
        $controllers->get('/authorize', [$auth, 'authorize'])
            ->bind('authorize');
        $controllers->get('/login-azure', [$auth, 'azureCallback'])
            ->bind('azureCallback');

        // Common service
        $common = new CommonController();
        $controllers->get('/', [$common, 'index']);
        $controllers->get('/welcome', [$common, 'getWelcomePage'])
            ->before(MiddlewareFactory::authRequired())
            ->bind('home');
        $controllers->get('/me', [$common, 'getMyInfo'])
            ->before(MiddlewareFactory::authRequired())
            ->bind('me');
        $controllers->post('/me', [$common, 'updateMyInfo'])
            ->before(MiddlewareFactory::authRequired());
        $controllers->get('/comm/user_list.ajax', [$common, 'userList'])
            ->before(MiddlewareFactory::authRequired());

        return $controllers;
    }
}
