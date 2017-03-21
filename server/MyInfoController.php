<?php
namespace Ridibooks\Cms;

use Ridibooks\Cms\Service\AdminUserService;
use Ridibooks\Cms\Thrift\ThriftService;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MyInfoController implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('/me', [$this, 'getMyInfo']);
		$controllers->post('/me', [$this, 'updateMyInfo']);

		return $controllers;
	}

	public function getMyInfo(CmsServerApplication $app)
	{
		$user_service = new AdminUserService();
		$user_info = $user_service->getUser(LoginService::GetAdminID());
		if (!$user_info->id) {
			return $app->redirect('/login?return_url=' . urlencode('/me'));
		}

		$user_info = ThriftService::convertUserToArray($user_info);
		return $app->render('me.twig', ['user_info' => $user_info]);
	}

	public function updateMyInfo(CmsServerApplication $app, Request $request)
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

		$sub_request = Request::create('/me');

		return $app->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
	}
}
