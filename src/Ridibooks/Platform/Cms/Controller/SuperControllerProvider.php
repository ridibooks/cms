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
use Ridibooks\Platform\Common\Base\JsonDto;
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
		$controllers->match('user_action.ajax', [$this, 'userAction']);

		$controllers->get('tags', [$this, 'tags']);
		$controllers->post('tags', [$this, 'createTag']);
		$controllers->delete('tags/{tag_id}', [$this, 'deleteTag']);
		$controllers->match('tag_action.ajax', [$this, 'tagAction']);

		$controllers->get('menu_list', [$this, 'menus']);
		$controllers->match('menu_action.ajax', [$this, 'menuAction']);

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

	public function userAction(Application $app, Request $request)
	{
		$jsonDto = new JsonDto();

		$adminUserService = new AdminUserService();
		$adminUserDto = new AdminUserDto($request);
		$adminUserAuthDto = new AdminUserAuthDto($request);

		try {
			switch ($adminUserDto->command) {
				case "insertUserInfo": //유저 정보 등록한다.
					$adminUserService->insertAdminUser($adminUserDto);
					$jsonDto->setMsg("성공적으로 등록하였습니다.");
					break;

				case "updateUserInfo": //유저 정보 수정한다.
					$adminUserService->updateAdminUser($adminUserDto);
					$jsonDto->setMsg("성공적으로 수정하였습니다.");
					break;

				case "insertUserAuth": //유저 권한 정보 등록한다.
					$adminUserService->insertAdminUserAuth($adminUserAuthDto);
					$jsonDto->setMsg("성공적으로 등록하였습니다.");
					break;

				case "delete":
					$adminUserService->deleteAdmin($adminUserDto);
					$jsonDto->setMsg("삭제되었습니다.");
					break;
			}

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

	public function tagAction(Application $app, Request $request)
	{
		$jsonDto = new JsonDto();

		$tagService = new AdminTagService();
		$tagDto = new AdminTagDto($request);
		$tagMenuDto = new AdminTagMenuDto($request);

		try {
			switch ($tagDto->command) {
				case 'insert':
					AdminTagService::insertTag($tagDto);
					$jsonDto->setMsg("성공적으로 등록하였습니다.");
					break;
				case 'update':
					AdminTagService::updateTag($tagDto);
					$jsonDto->setMsg("성공적으로 수정하였습니다.");
					break;
				case 'show_mapping': //Tag에 매핑된 메뉴 리스트
					$jsonDto->data = [
						'menus' => $tagService->getMappedAdminMenuListForSelectBox($tagDto->id),
						'admins' => $tagService->getMappedAdmins($tagDto->id)
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
		return $app->render('super/menu_list.twig',
			[
				'title' => '메뉴 관리',
				'menu_list' => AdminMenuService::getMenuList()
			]
		);
	}

	public function menuAction(Application $app, Request $request)
	{
		$menu_service = new AdminMenuService();
		$json_dto = new JsonDto();

		$menu_dto = new AdminMenuDto($request);
		$menu_ajax_dto = new AdminMenuAjaxDto($request);

		try {
			switch ($menu_dto->command) {
				case 'insert': //메뉴 등록
					$menu_service->insertMenu($menu_dto);
					$json_dto->setMsg('성공적으로 등록하였습니다.');
					break;
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
