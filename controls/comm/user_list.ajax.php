<?php

use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Common\Base\JsonDto;

$adminUserService = new AdminUserService();
$jsonDto = new JsonDto();

try{
	$jsonDto->data = (array) $adminUserService->getAllAdminUserArray();
} catch (Exception $e) {
	$jsonDto->setException($e);
}

return json_encode(
	(array)$jsonDto
);
