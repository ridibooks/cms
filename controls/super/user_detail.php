<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$adminUserService = new AdminUserService();
$admin_id = $request->get("id");

//유저 상세 정보
$userDetail = $adminUserService->getAdminUser($admin_id);
if (!$userDetail) {
	return UrlHelper::printAlertRedirect('/super/user_list', '존재하지 않는 사용자입니다.');
}

//유저 태그 매핑 정보
$userTag = $adminUserService->getAdminUserTag($admin_id);
//유저 메뉴 매핑 정보
$userMenu = $adminUserService->getAdminUserMenu($admin_id);

return compact(
	"admin_id",
	"userDetail",
	"userTag",
	"userMenu",
	"page",
	"search_text"
);
