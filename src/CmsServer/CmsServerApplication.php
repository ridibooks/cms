<?php
namespace Ridibooks\CmsServer;

use Illuminate\Database\Capsule;
use Silex\Application;
use Silex\Application\TwigTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CmsServerApplication extends Application
{
	use TwigTrait;

	public function __construct(array $values = [])
	{
		parent::__construct($values);

		$this->bootstrap();
		$this->setDefaultErrorHandler();

		$this->mount('/', new CmsServerController());
	}

	private function bootstrap()
	{
		$mysql = $this['mysql'];

		$capsule = new Capsule\Manager();
		$capsule->addConnection([
			'driver'    => 'mysql',
			'host'      => $mysql['host'],
			'database'  => $mysql['database'],
			'username'  => $mysql['user'],
			'password'  => $mysql['password'],
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
			'options'   => [
				// mysqlnd 5.0.12-dev - 20150407 에서 PDO->prepare 가 매우 느린 현상
				\PDO::ATTR_EMULATE_PREPARES => true
			]
		]);

		$capsule->setAsGlobal();
		$capsule->bootEloquent();

		ini_set('max_execution_time', 300);
		ini_set('max_input_time', 60);

		mb_internal_encoding('UTF-8');
		mb_regex_encoding("UTF-8");
	}

	private function setDefaultErrorHandler()
	{
		$this->error(function (\Exception $e) {
			if ($this['debug']) {
				return null;
			}

			if ($e instanceof HttpException) {
				return Response::create($e->getMessage(), $e->getStatusCode(), $e->getHeaders());
			}

			throw $e;
		});
	}
}
