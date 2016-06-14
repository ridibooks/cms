<?php
use Ridibooks\Platform\Cms\Auth\AdminTagService;
use Ridibooks\Platform\Cms\Auth\Dto\AdminTagDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminTagMenuDto;
use Ridibooks\Platform\Cms\Auth\MenuService;
use Ridibooks\Platform\Common\Base\JsonDto;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$jsonDto = new JsonDto();

$tagService = new AdminTagService();
$menuService = new MenuService();
$tagDto = new AdminTagDto($request);
$tagMenuDto = new AdminTagMenuDto($request);


try {
	switch ($tagDto->command) {
		case 'insert': //Tag 등록
			$tagService->insertTag($tagDto);
			$jsonDto->setMsg("성공적으로 등록하였습니다.");
			break;
		case 'update': //Tag 수정
			$tagService->updateTag($tagDto);
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
} catch (Exception $e) {
	$jsonDto->setException($e);
}

return json_encode(
	(array)$jsonDto
);
