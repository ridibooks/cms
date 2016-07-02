<?php
use Ridibooks\Platform\Cms\Auth\AdminUserService;
use Ridibooks\Platform\Common\PagingUtil;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$page = $request->get('page');
$search_text = $request->get("search_text");

$pagingDto = new PagingUtil(AdminUserService::getAdminUserCount($search_text), $page, null, 20);

$admin_user_list = AdminUserService::getAdminUserList($search_text, $pagingDto->start, $pagingDto->limit);
$paging = AdminUserService::getPagingTagByPagingDtoNew($pagingDto);

return [
	'admin_user_list' => $admin_user_list,
	'paging' => $paging,
	'page' => $page,
	'search_text' => $search_text
];
