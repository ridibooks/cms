<?php
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\Dto\AdminUserDto;
use Ridibooks\Platform\Common\Base\JsonDto;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$adminUserDto = new AdminUserDto($request);
$jsonDto = new JsonDto();
$adminUserService = new AdminUserService();

try{
	$adminUserService->updateUserInfo($adminUserDto);
	$jsonDto->setMsg('성공적으로 수정하였습니다.');
} catch (Exception $e) {
	$jsonDto->setException($e);
}

return json_encode(
	(array)$jsonDto
);
