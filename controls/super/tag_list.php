<?php
use Ridibooks\Platform\Cms\Auth\AdminTagService;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$tag_list = AdminTagService::getTagListWithUseCount();

return [
	'title' => '태그 관리',
	'tag_list' => $tag_list
];
