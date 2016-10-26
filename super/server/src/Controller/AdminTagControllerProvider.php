<?php
namespace Ridibooks\Platform\Cms\Controller;

use Ridibooks\Platform\Cms\Auth\AdminTagService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Common\Base\JsonDto;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTagControllerProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		/** @var ControllerCollection $controllers */
		$controllers = $app['controllers_factory'];

		$controllers->get('tags', [$this, 'tags']);
		$controllers->post('tags', [$this, 'createTag']);
		$controllers->put('tags/{tag_id}', [$this, 'updateTag']);
		$controllers->delete('tags/{tag_id}', [$this, 'deleteTag']);
		$controllers->get('tags/{tag_id}/users', [$this, 'tagUsers']);
		$controllers->get('tags/{tag_id}/menus', [$this, 'tagMenus']);
		$controllers->put('tags/{tag_id}/menus/{menu_id}', [$this, 'addTagMenu']);
		$controllers->delete('tags/{tag_id}/menus/{menu_id}', [$this, 'deleteTagMenu']);

		return $controllers;
	}

	public function tags(CmsApplication $app, Request $request)
	{
		if (in_array('application/json', $request->getAcceptableContentTypes())) {
			return $app->json(AdminTagService::getAllTags());
		}

		return $app->render('super/tags.twig',
			[
				'title' => '태그 관리',
				'tags' => AdminTagService::getTagListWithUseCount()
			]
		);
	}

	public function createTag(CmsApplication $app, Request $request)
	{
		$name = $request->get('name');
		$is_use = $request->get('is_use');

		try {
			AdminTagService::insertTag($name, $is_use);
			$app->addFlashInfo('성공적으로 등록하였습니다.');
		} catch (\Exception $e) {
			$app->addFlashError($e->getMessage());
		}

		return $app->redirect('/super/tags');
	}

	public function updateTag(CmsApplication $app, Request $request, $tag_id)
	{
		$name = $request->get('name');
		$is_use = $request->request->getBoolean('is_use');

		$jsonDto = new JsonDto();

		try {
			AdminTagService::updateTag($tag_id, $name, $is_use);
			$jsonDto->setMsg("성공적으로 수정하였습니다.");
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}

	public function deleteTag($tag_id, CmsApplication $app)
	{
		$jsonDto = new JsonDto();

		try {
			AdminTagService::deleteTag($tag_id);
			$jsonDto->setMsg('성공적으로 삭제되었습니다.');
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}

	public function tagUsers($tag_id, Application $app)
	{
		$json = new JsonDto();
		$json->data = AdminTagService::getMappedAdmins($tag_id);

		return $app->json((array)$json);
	}

	public function tagMenus(Application $app, $tag_id)
	{
		$jsonDto = new JsonDto();
		$jsonDto->data = [
			'menus' => AdminTagService::getMappedAdminMenuListForSelectBox($tag_id),
			'admins' => AdminTagService::getMappedAdmins($tag_id)
		];

		return $app->json((array)$jsonDto);
	}

	public function addTagMenu(CmsApplication $app, $tag_id, $menu_id)
	{
		$jsonDto = new JsonDto();
		try {
			AdminTagService::insertTagMenu($tag_id, $menu_id);
		} catch (\Exception $e) {
			$jsonDto->setException($e);
		}

		return $app->json((array)$jsonDto);
	}

	public function deleteTagMenu(Application $app, $tag_id, $menu_id)
	{
		try {
			AdminTagService::deleteTagMenu($tag_id, $menu_id);
		} catch (\Exception $e) {
			return $app->abort(Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return Response::create(Response::HTTP_NO_CONTENT);
	}
}
