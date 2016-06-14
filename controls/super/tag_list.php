<?php
use Ridibooks\Platform\Cms\Auth\AdminTagService;
use Ridibooks\Platform\Cms\Auth\Dto\AdminTagDto;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$tagService = new AdminTagService();
$tagDto = new AdminTagDto($request);

$tag_list = $tagService->getTagListWithUseCount();

return compact(
	'tag_list'
);
