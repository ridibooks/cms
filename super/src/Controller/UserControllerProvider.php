<?php
namespace Ridibooks\Platform\Cms\Controller;

use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\Dto\AdminUserDto;
use Ridibooks\Platform\Common\Base\JsonDto;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('user_info', [$this, 'user']);
		$controllers->get('user_list.ajax', [$this, 'userList']);
		$controllers->match('user_info_action.ajax', [$this, 'updateAction']);

		return $controllers;
	}

	public function user(CmsApplication $app)
	{
		$adminUserService = new AdminUserService();
		$user_info = $adminUserService->getAdminUser(LoginService::GetAdminID());

		return $app->render('comm/user_info.twig',
			[
				'user_info' => $user_info
			]
		);
	}

	public function userList(CmsApplication $app)
	{
		$jsonDto = new JsonDto();

		try {
			$jsonDto->data = AdminUserService::getAllAdminUserArray();
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}

	public function updateAction(CmsApplication $app, Request $request)
	{
		$adminUserDto = new AdminUserDto($request);
		$jsonDto = new JsonDto();

		try {
			AdminUserService::updateUserInfo($adminUserDto);
			$jsonDto->setMsg('성공적으로 수정하였습니다.');
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}
}
