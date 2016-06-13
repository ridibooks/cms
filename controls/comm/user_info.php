<?php

use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Cms\Auth\LoginService;

$adminUserService = new AdminUserService();
$user_info = $adminUserService->getAdminUser(LoginService::GetAdminID());

return compact(
	'user_info'
);
