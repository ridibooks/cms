<?php
namespace Ridibooks\Platform\Cms\Controller;

use Ridibooks\Platform\Cms\Auth\AdminMenuService;
use Ridibooks\Platform\Cms\Auth\AdminTagService;
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Common\PagingUtil;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class SuperControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('user_list', [$this, 'users']);
		$controllers->get('user_detail', [$this, 'user']);

		$controllers->get('tag_list', [$this, 'tags']);

		$controllers->get('menu_list', [$this, 'menus']);

		return $controllers;
	}

	public function users(CmsApplication $app, Request $request)
	{
		$page = $request->get('page');
		$search_text = $request->get("search_text");

		$pagingDto = new PagingUtil(AdminUserService::getAdminUserCount($search_text), $page, null, 20);

		$admin_user_list = AdminUserService::getAdminUserList($search_text, $pagingDto->start, $pagingDto->limit);
		$paging = AdminUserService::getPagingTagByPagingDtoNew($pagingDto);

		return $app->render('super/user_list.twig',
			[
				'admin_user_list' => $admin_user_list,
				'paging' => $paging,
				'page' => $page,
				'search_text' => $search_text
			]
		);
	}

	public function user(CmsApplication $app, Request $request)
	{
		$adminUserService = new AdminUserService();
		$admin_id = $request->get("id");

		$userDetail = $adminUserService->getAdminUser($admin_id);
		$userTag = [];
		$userMenu = [];
		if ($userDetail) {
			// 유저 태그 매핑 정보
			$tags = AdminUserService::getAdminUserTag($admin_id);
			$userTag = implode(',', $tags);

			// 유저 메뉴 매핑 정보
			$menus = AdminUserService::getAdminUserMenu($admin_id);
			$userMenu = implode(',', $menus);
		}

		return $app->render('super/user_detail.twig',
			[
				'admin_id' => $admin_id,
				'userDetail' => $userDetail,
				'userTag' => $userTag,
				'userMenu' => $userMenu,
				'page'
			]
		);
	}

	public function tags(CmsApplication $app)
	{
		return $app->render('super/tag_list.twig',
			[
				'title' => '태그 관리',
				'tag_list' => AdminTagService::getTagListWithUseCount()
			]
		);
	}

	public function menus(CmsApplication $app)
	{
		return $app->render('super/menu_list.twig',
			[
				'title' => '메뉴 관리',
				'menu_list' => AdminMenuService::getMenuList()
			]
		);
	}
}