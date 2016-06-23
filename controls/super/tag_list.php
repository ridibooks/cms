<?php
use Ridibooks\Platform\Cms\Auth\AdminTagService;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$tag_list = AdminTagService::getTagListWithUseCount();

return compact(
	'tag_list'
);
