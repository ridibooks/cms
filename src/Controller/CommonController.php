<?php

namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\Service\AdminUserService;
use Ridibooks\Cms\Service\LoginService;
use Ridibooks\Cms\Thrift\ThriftService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CommonController
{
    public function index(Application $app)
    {
        return $app->redirect($app['url_generator']->generate('home'));
    }

    public function getWelcomePage(Application $app)
    {
        return $app->render('welcome.twig');
    }

    public function userList(Application $app)
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

    public function getMyInfo(Application $app)
    {
        $user_service = new AdminUserService();
        $user_info = $user_service->getUser(LoginService::GetAdminID());
        if (!$user_info->id) {
            $me_path = $app['url_generator']->generate('me');
            $login_path = $app['url_generator']->generate('login');

            return $app->redirect($login_path . '?return_url=' . urlencode($me_path));
        }

        $user_info = ThriftService::convertUserToArray($user_info);

        return $app->render('me.twig', ['user_info' => $user_info]);
    }

    public function updateMyInfo(Application $app, Request $request)
    {
        $name = $request->get('name');
        $team = $request->get('team');
        $is_use = $request->get('is_use');

        try {
            $passwd = '';
            $new_passwd = trim($request->get('new_passwd'));
            $chk_passwd = trim($request->get('chk_passwd'));
            if (!empty($new_passwd)) {
                if ($new_passwd != $chk_passwd) {
                    throw new \Exception('변경할 비밀번호가 일치하지 않습니다.');
                }
                $passwd = $new_passwd;
            }
            $user_service = new AdminUserService();
            $user_service->updateMyInfo($name, $team, $is_use, $passwd);
            $app->addFlashInfo('성공적으로 수정하였습니다.');
        } catch (\Exception $e) {
            $app->addFlashError($e->getMessage());
        }

        $sub_request = Request::create($app['url_generator']->generate('me'));

        return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
    }
}
