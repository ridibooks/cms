<?php
namespace Ridibooks\Cms;

use Illuminate\Database\Capsule;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\Thrift\ThriftResponse;
use Silex\Application;
use Silex\Application\TwigTrait;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CmsServerApplication extends Application
{
	use TwigTrait;

	public function __construct(array $values = [])
	{
		parent::__construct($values);

		$this->bootstrap();
		$this->registerSentryServiceProvider();
		$this->registerSessionServiceProvider();
		$this->registerTwigServiceProvider();
		$this->setDefaultErrorHandler();

		// thrift proxy
		$this->post('/', function (Request $request) {
			return ThriftResponse::create($request);
		});

		// web server
		$this->mount('/', new LoginController());
		$this->mount('/', new MyInfoController());
		$this->mount('/', new CommonController());
	}

	private function bootstrap()
	{
		$mysql = $this['mysql'];

		$capsule = new Capsule\Manager();
		$capsule->addConnection([
			'driver' => 'mysql',
			'host' => $mysql['host'],
			'database' => $mysql['database'],
			'username' => $mysql['user'],
			'password' => $mysql['password'],
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => '',
			'options' => [
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

	private function registerSentryServiceProvider()
	{
		$sentry_dsn = $this['sentry_key'];
		if (isset($sentry_dsn) && $sentry_dsn!=='') {
			$this->register(new SentryServiceProvider, array(
				SentryServiceProvider::SENTRY_OPTIONS => array(
					SentryServiceProvider::OPT_DSN => $sentry_dsn,
				)
			));

			$client = $this[SentryServiceProvider::SENTRY];
			$client->install();
		}
	}

	private function registerTwigServiceProvider()
	{
		$this->register(
			new TwigServiceProvider(),
			[
				'twig.env.globals' => [],
				'twig.options' => [
					'cache' => sys_get_temp_dir() . '/twig_cache_v12',
					'auto_reload' => true,
					// TwigServiceProvider에서 기본으로 $this['debug']와 같게 설정되어 있는데 true 일경우
					// if xxx is defined로 변수를 일일이 체크해줘야 하는 문제가 있어서 override 함
					'strict_variables' => false
				]
			]
		);

		// see http://silex.sensiolabs.org/doc/providers/twig.html#customization
		$this['twig'] = $this->extend(
			'twig',
			function (\Twig_Environment $twig) {
				$globals = array_merge($this->getTwigGlobalVariables(), $this['twig.env.globals']);
				foreach ($globals as $k => $v) {
					$twig->addGlobal($k, $v);
				}

				return $twig;
			}
		);

		$this['twig.loader.filesystem'] = $this->extend(
			'twig.loader.filesystem',
			function (\Twig_Loader_Filesystem $loader) {
				$loader->addPath(__DIR__ . '/../views/');

				return $loader;
			}
		);
	}

	private function getTwigGlobalVariables()
	{
		$globals = [
			'STATIC_URL' => '/static',
			'BOWER_PATH' => '/static/bower_components',
		];

		if (isset($_SESSION['session_user_menu'])) {
			$globals['session_user_menu'] = $_SESSION['session_user_menu'];
		}

		return $globals;
	}

	private function registerSessionServiceProvider()
	{
		$this->register(
			new SessionServiceProvider(),
			[
				'session.storage.handler' => null
			]
		);

		$this['flashes'] = $this->getFlashBag()->all();
	}

	public function addFlashInfo($message)
	{
		$this->getFlashBag()->add('info', $message);
	}

	public function addFlashSuccess($message)
	{
		$this->getFlashBag()->add('success', $message);
	}

	public function addFlashWarning($message)
	{
		$this->getFlashBag()->add('warning', $message);
	}

	public function addFlashError($message)
	{
		$this->getFlashBag()->add('danger', $message);
	}

	public function getFlashBag(): FlashBag
	{
		return $this['session']->getFlashBag();
	}
}
