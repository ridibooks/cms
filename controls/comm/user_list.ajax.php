<?php

use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Common\Base\JsonDto;

$jsonDto = new JsonDto();

try{
	$jsonDto->data = AdminUserService::getAllAdminUserArray();
} catch (Exception $e) {
	$jsonDto->setException($e);
}

return json_encode(
	(array)$jsonDto
);
