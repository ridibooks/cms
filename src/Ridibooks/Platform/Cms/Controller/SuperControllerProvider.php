<?php
namespace Ridibooks\Platform\Cms\Controller;

use Ridibooks\Platform\Cms\Auth\AdminMenuService;
use Ridibooks\Platform\Cms\Auth\AdminTagService;
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\Dto\AdminMenuAjaxDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminMenuDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminTagDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminTagMenuDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserAuthDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\PaginationHelper;
use Ridibooks\Platform\Common\Base\JsonDto;
use Ridibooks\Platform\Common\PagingUtil;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SuperControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('users', [$this, 'users']);
		$controllers->get('user_list', [$this, 'users']);
		$controllers->get('users/{user_id}', [$this, 'user']);
		$controllers->post('users/new', [$this, 'createUser']);
		$controllers->post('users/{user_id}', [$this, 'updateUser']);
		$controllers->delete('users/{user_id}', [$this, 'deleteUser']);
		$controllers->match('user_action.ajax', [$this, 'userAction']);

		$controllers->get('tags', [$this, 'tags']);
		$controllers->post('tags', [$this, 'createTag']);
		$controllers->delete('tags/{tag_id}', [$this, 'deleteTag']);
		$controllers->get('tags/{tag_id}/users', [$this, 'tagUsers']);
		$controllers->match('tag_action.ajax', [$this, 'tagAction']);

		$controllers->get('menus', [$this, 'menus']);
		$controllers->get('menu_list', [$this, 'menus']);
		$controllers->post('menus', [$this, 'createMenu']);
		$controllers->match('menu_action.ajax', [$this, 'menuAction']);

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

	/**
	 * @deprecated
	 * @param Application $app
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function userAction(Application $app, Request $request)
	{
		$jsonDto = new JsonDto();

		try {
			$adminUserAuthDto = new AdminUserAuthDto($request);
			$adminUserAuthDto->id = $request->get('id');
			AdminUserService::insertAdminUserAuth($adminUserAuthDto);
			$jsonDto->setMsg("성공적으로 등록하였습니다.");
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}

	public function tags(CmsApplication $app)
	{
		return $app->render('super/tags.twig',
			[
				'title' => '태그 관리',
				'tags' => AdminTagService::getTagListWithUseCount()
			]
		);
	}

	public function createTag(CmsApplication $app, Request $request)
	{
		$tagDto = new AdminTagDto($request);

		try {
			AdminTagService::insertTag($tagDto);
			$app->addFlashInfo('성공적으로 등록하였습니다.');
		} catch (\Exception $e) {
			$app->addFlashError($e->getMessage());
		}

		return $app->redirect('/super/tags');
	}

	public function deleteTag($tag_id, CmsApplication $app)
	{
		$jsonDto = new JsonDto();

		try {
			AdminTagService::deleteTag($tag_id);
			$jsonDto->setMsg('성공적으로 삭제되었습니다.');
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}

	public function tagUsers($tag_id, Application $app)
	{
		$json = new JsonDto();
		$json->data = AdminTagService::getMappedAdmins($tag_id);

		return $app->json((array)$json);
	}

	public function tagAction(Application $app, Request $request)
	{
		$jsonDto = new JsonDto();

		$tagService = new AdminTagService();
		$tagDto = new AdminTagDto($request);
		$tagMenuDto = new AdminTagMenuDto($request);

		try {
			switch ($tagDto->command) {
				case 'update':
					AdminTagService::updateTag($tagDto);
					$jsonDto->setMsg("성공적으로 수정하였습니다.");
					break;
				case 'show_mapping': //Tag에 매핑된 메뉴 리스트
					$jsonDto->data = [
						'menus' => $tagService->getMappedAdminMenuListForSelectBox($tagDto->id),
						'admins' => AdminTagService::getMappedAdmins($tagDto->id)
					];
					break;
				case 'mapping_tag_menu': //메뉴를 Tag에 매핑시킨다.
					$tagService->insertTagMenu($tagMenuDto);
					break;
				case 'delete_tag_menu': //메뉴를 Tag에서 삭제한다.
					$tagService->deleteTagMenu($tagMenuDto);
					break;
				case "showTagArray": //전체 Tag 목록 가져온다.
					$jsonDto->data = (array)$tagService->getTagList();
					break;
			}
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}

	public function menus(CmsApplication $app)
	{
		return $app->render('super/menus.twig',
			[
				'title' => '메뉴 관리',
				'menu_list' => AdminMenuService::getMenuList()
			]
		);
	}

	public function createMenu(CmsApplication $app, Request $request)
	{
		$menu_dto = new AdminMenuDto($request);

		try {
			AdminMenuService::insertMenu($menu_dto);
			$app->addFlashInfo('성공적으로 등록하였습니다.');
		} catch (\Exception $e) {
			$app->addFlashError($e->getMessage());
		}

		return $app->redirect('/super/menus');
	}

	public function menuAction(Application $app, Request $request)
	{
		$menu_service = new AdminMenuService();
		$json_dto = new JsonDto();

		$menu_dto = new AdminMenuDto($request);
		$menu_ajax_dto = new AdminMenuAjaxDto($request);

		try {
			switch ($menu_dto->command) {
				case 'update': //메뉴 수정
					$menu_service->updateMenu($menu_dto);
					$json_dto->setMsg('성공적으로 수정하였습니다.');
					break;
				case 'show_ajax_list': //Ajax 메뉴 리스트
					$json_dto->data = $menu_service->getMenuAjaxList($menu_ajax_dto->menu_id);
					break;
				case 'ajax_insert': //Ajax 메뉴 등록
					$menu_service->insertMenuAjax($menu_ajax_dto);
					$json_dto->setMsg('성공적으로 등록하였습니다.');
					break;
				case 'ajax_update': //Ajax 메뉴 수정
					$menu_service->updateMenuAjax($menu_ajax_dto);
					$json_dto->setMsg('성공적으로 수정하였습니다.');
					break;
				case 'ajax_delete': //Ajax 메뉴 삭제
					$menu_service->deleteMenuAjax($menu_ajax_dto);
					$json_dto->setMsg('성공적으로 삭제하였습니다.');
					break;
				case "showMenuArray": //전체 메뉴 목록 가져온다.
					$json_dto->data = (array)AdminMenuService::getMenuList(1);
					break;
			}

		} catch (\Exception $e) {
			$json_dto->setException($e);
		}

		return $app->json((array)$json_dto);
	}
}
