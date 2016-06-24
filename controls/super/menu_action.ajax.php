<?php
use Ridibooks\Platform\Cms\Auth\Dto\AdminMenuAjaxDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminMenuDto;
use Ridibooks\Platform\Cms\Auth\MenuService;
use Ridibooks\Platform\Common\Base\JsonDto;
use Symfony\Component\HttpFoundation\Request;

$menu_service = new MenuService();
$request = Request::createFromGlobals();
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
			$json_dto->data = (array)$menu_service->getMenuAjaxList($menu_ajax_dto->menu_id);
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
			$json_dto->data = (array)MenuService::getMenuList(1);
			break;
	}

} catch (Exception $e) {
	$json_dto->setException($e);
}

return json_encode(
	(array)$json_dto
);
