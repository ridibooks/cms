<?php

namespace Ridibooks\CmsServer;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ridibooks\CmsServer\Service\AdminAuthService;
use Ridibooks\CmsServer\Service\AdminMenuService;
use Ridibooks\CmsServer\Service\AdminTagService;
use Ridibooks\CmsServer\Service\AdminUserService;
use Ridibooks\CmsServer\Thrift\AdminAuth\AdminAuthServiceProcessor;
use Ridibooks\CmsServer\Thrift\AdminMenu\AdminMenuServiceProcessor;
use Ridibooks\CmsServer\Thrift\AdminTag\AdminTagServiceProcessor;
use Ridibooks\CmsServer\Thrift\AdminUser\AdminUserServiceProcessor;
use Ridibooks\CmsServer\Thrift\ThriftResponse;

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
