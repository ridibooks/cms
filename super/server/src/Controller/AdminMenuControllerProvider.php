<?php
namespace Ridibooks\Platform\Cms\Controller;

use Ridibooks\Platform\Cms\Auth\AdminMenuService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\Dto\AdminMenuAjaxDto;
use Ridibooks\Platform\Cms\Dto\AdminMenuDto;
use Ridibooks\Platform\Common\Base\JsonDto;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminMenuControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('menus', [$this, 'menus']);
		$controllers->post('menus', [$this, 'createMenu']);
		$controllers->match('menu_action.ajax', [$this, 'menuAction']);

		$controllers->get('menus/{menu_id}/submenus', [$this, 'submenus']);
		$controllers->delete('menus/{menu_id}/submenus/{submenu_id}', [$this, 'deleteSubmenu']);

		return $controllers;
	}

	public function menus(CmsApplication $app, Request $request)
	{
		if (in_array('application/json', $request->getAcceptableContentTypes())) {
			return $app->json(AdminMenuService::getMenuList(1));
		}

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

	public function submenus(CmsApplication $app, $menu_id)
	{
		$json = new JsonDto();
		$json->data = AdminMenuService::getMenuAjaxList($menu_id);

		return $app->json((array)$json);
	}

	public function deleteSubmenu(CmsApplication $app, $menu_id, $submenu_id)
	{
		$json_dto = new JsonDto();
		try {
			AdminMenuService::deleteMenuAjax($menu_id, $submenu_id);
			$json_dto->setMsg('성공적으로 삭제하였습니다.');

		} catch (\Exception $e) {
			$json_dto->setException($e);
		}

		return $app->json((array)$json_dto);
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
				case 'ajax_insert': //Ajax 메뉴 등록
					$menu_service->insertMenuAjax($menu_ajax_dto);
					$json_dto->setMsg('성공적으로 등록하였습니다.');
					break;
				case 'ajax_update': //Ajax 메뉴 수정
					$menu_service->updateMenuAjax($menu_ajax_dto);
					$json_dto->setMsg('성공적으로 수정하였습니다.');
					break;
			}

		} catch (\Exception $e) {
			$json_dto->setException($e);
		}

		return $app->json((array)$json_dto);
	}
}
