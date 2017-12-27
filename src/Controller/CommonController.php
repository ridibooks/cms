<?php
namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Service\AdminUserService;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class CommonController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/', [$this, 'index']);
        $controllers->get('/welcome', [$this, 'getWelcomePage']);
        $controllers->get('/comm/user_list.ajax', [$this, 'userList']);

        return $controllers;
    }

    public function index(CmsApplication $app)
    {
        return $app->redirect('/welcome');
    }

    public function getWelcomePage(CmsApplication $app)
    {
        return $app->render('welcome.twig');
    }

    public function userList(CmsApplication $app)
    {
        $result = [];

        try {
            $user_service = new AdminUserService();
            $result['data'] = $user_service->getAllAdminUserArray();
            $result['success'] = true;
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['msg'] = [$e->getMessage()];
        }

        return $app->json((array)$result);
    }
}
