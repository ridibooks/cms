<?php
namespace Ridibooks\Platform\Cms;

use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UserControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('me', [$this, 'getMyInfo']);
		$controllers->post('me', [$this, 'updateMyInfo']);

		$controllers->get('comm/user_list.ajax', [$this, 'userList']);

		return $controllers;
	}

	public function getMyInfo(CmsApplication $app)
	{
		$user_info = AdminUserService::getUser(LoginService::GetAdminID());

		return $app->render('me.twig',
			[
				'user_info' => $user_info
			]
		);
	}

	public function updateMyInfo(CmsApplication $app, Request $request)
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
			AdminUserService::updateMyInfo($name, $team, $is_use, $passwd);
			$app->addFlashInfo('성공적으로 수정하였습니다.');
		} catch (\Exception $e) {
			$app->addFlashError($e->getMessage());
		}

		$subRequest = Request::create('/me');
		return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
	}

	public function userList(CmsApplication $app)
	{
		$result = [];

		try {
			$result['data'] = AdminUserService::getAllAdminUserArray();
			$result['success'] = true;
		} catch (\Exception $e) {
			$result['success'] = false;
			$result['msg'] = [$e->getMessage()];
		}

		return $app->json((array)$jsonDto);
	}
}
