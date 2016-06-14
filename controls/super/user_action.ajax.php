<?php
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserAuthDto;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Common\Base\JsonDto;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
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
	}

} catch (Exception $e) {
	$jsonDto->setException($e);
}

return json_encode(
	(array)$jsonDto
);
