<?php

namespace Ridibooks\Cms\Server;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ridibooks\Cms\Server\Service\AdminAuthService;
use Ridibooks\Cms\Server\Service\AdminMenuService;
use Ridibooks\Cms\Server\Service\AdminTagService;
use Ridibooks\Cms\Server\Service\AdminUserService;
use Ridibooks\Cms\Thrift\AdminAuth\AdminAuthServiceProcessor;
use Ridibooks\Cms\Thrift\AdminMenu\AdminMenuServiceProcessor;
use Ridibooks\Cms\Thrift\AdminTag\AdminTagServiceProcessor;
use Ridibooks\Cms\Thrift\AdminUser\AdminUserServiceProcessor;
use Ridibooks\Cms\Thrift\ThriftResponse;

class CmsServerController implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller_collection = $app['controllers_factory'];
		$controller_collection->post('/auth', [$this, 'auth']);
		$controller_collection->post('/menu', [$this, 'menu']);
		$controller_collection->post('/tag', [$this, 'tag']);
		$controller_collection->post('/user', [$this, 'user']);

		return $controller_collection;
	}

	public function auth(Request $request, Application $app)
	{
		$service = new AdminUserService();
		$processor = new AdminUserServiceProcessor($service);
		return ThriftResponse::make($request, $processor, 'json');
	}

	public function menu(Request $request, Application $app)
	{
		$service = new AdminMenuService();
		$processor = new AdminMenuServiceProcessor($service);
		return ThriftResponse::make($request, $processor, 'json');
	}

	public function tag(Request $request, Application $app)
	{
		$service = new AdminTagService();
		$processor = new AdminTagServiceProcessor($service);
		return ThriftResponse::make($request, $processor, 'json');
	}

	public function user(Request $request, Application $app)
	{
		$service = new AdminUserService();
		$processor = new AdminUserServiceProcessor($service);
		return ThriftResponse::make($request, $processor, 'json');
	}
}
