<?php
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$adminUserService = new AdminUserService();
$admin_id = $request->get("id");

// 유저 상세 정보
$userDetail = $adminUserService->getAdminUser($admin_id);
if ($userDetail) {
	// 유저 태그 매핑 정보
	$tags = AdminUserService::getAdminUserTag($admin_id);
	$userTag = implode(',', $tags);

	// 유저 메뉴 매핑 정보
	$menus = AdminUserService::getAdminUserMenu($admin_id);
	$userMenu = implode(',', $menus);
}

return compact(
	"admin_id",
	"userDetail",
	"userTag",
	"userMenu",
	"page",
	"search_text"
);
