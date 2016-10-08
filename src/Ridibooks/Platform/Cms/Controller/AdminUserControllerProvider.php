<?php
namespace Ridibooks\Platform\Cms\Controller;

use Ridibooks\Library\Util;
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\PaginationHelper;
use Ridibooks\Platform\Common\PagingUtil;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AdminUserControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('users', [$this, 'users']);
		$controllers->get('users/{user_id}', [$this, 'user']);
		$controllers->post('users/new', [$this, 'createUser']);
		$controllers->post('users/{user_id}', [$this, 'updateUser']);
		$controllers->delete('users/{user_id}', [$this, 'deleteUser']);
		$controllers->post('users/{user_id}/permissions', [$this, 'updateUserPermissions']);

		return $controllers;
	}

	public function users(CmsApplication $app, Request $request)
	{
		$page = $request->get('page');
		$search_text = $request->get("search_text");

		$pagingDto = new PagingUtil(AdminUserService::getAdminUserCount($search_text), $page, null, 50);

		$admin_user_list = AdminUserService::getAdminUserList($search_text, $pagingDto->start, $pagingDto->limit);
		$paging = PaginationHelper::getArgs($request, $pagingDto->total, $pagingDto->cpage, $pagingDto->line_per_page);

		return $app->render('super/users.twig',
			[
				'admin_user_list' => $admin_user_list,
				'paging' => $paging,
				'page' => $page,
				'search_text' => $search_text
			]
		);
	}

	public function user(CmsApplication $app, $user_id)
	{
		if ($user_id === 'new') {
			$user_id = '';
			$user = null;
			$tags = [];
			$menus = [];
		} else {
			$user = AdminUserService::getUser($user_id);
			if (!$user) {
				return $app->abort(Response::HTTP_NOT_FOUND);
			}
			$tags = AdminUserService::getAdminUserTag($user_id);
			$menus = AdminUserService::getAdminUserMenu($user_id);
		}

		return $app->render('super/user_edit.twig',
			[
				'admin_id' => $user_id,
				'userDetail' => $user,
				'userTag' => implode(',', $tags),
				'userMenu' => implode(',', $menus),
			]
		);
	}

	public function createUser(CmsApplication $app, Request $request)
	{
		$user_id = $request->get('id');

		try {
			$adminUserDto = new AdminUserDto($request);
			$adminUserDto->id = $user_id;
			AdminUserService::insertAdminUser($adminUserDto);
		} catch (\Exception $e) {
			$app->addFlashError($e->getMessage());
		}

		return $app->redirect('/super/users/' . $user_id);
	}

	public function updateUser(CmsApplication $app, Request $request, $user_id)
	{
		$user = AdminUserService::getUser($user_id);
		if (!$user) {
			return $app->abort(Response::HTTP_NOT_FOUND);
		}

		try {
			$adminUserDto = new AdminUserDto($request);
			$adminUserDto->id = $user_id;
			AdminUserService::updateUserInfo($adminUserDto);
			$app->addFlashInfo('성공적으로 수정하였습니다.');
		} catch (\Exception $e) {
			$app->addFlashError($e->getMessage());
		}

		$subRequest = Request::create('/super/users/' . $user_id);
		return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
	}

	public function deleteUser(CmsApplication $app, $user_id)
	{
		$user = AdminUserService::getUser($user_id);
		if (!$user) {
			return $app->abort(Response::HTTP_NOT_FOUND);
		}

		try {
			AdminUserService::deleteUser($user_id);
		} catch (\Exception $e) {
			return $app->abort(Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return Response::create(Response::HTTP_NO_CONTENT);
	}

	public function updateUserPermissions(CmsApplication $app, Request $request, $user_id)
	{
		try {
			$tag_ids = Util::splitAndIntval(',', $request->get('tag_ids'));
			$menu_ids = Util::splitAndIntval(',', $request->get('menu_ids'));
			AdminUserService::updateUserPermissions($user_id, $tag_ids, $menu_ids);
			$app->addFlashInfo('성공적으로 수정하였습니다.');
		} catch (\Exception $e) {
			$app->addFlashError($e->getMessage());
		}

		return $app->redirect('/super/users/' . $user_id);
	}
}
