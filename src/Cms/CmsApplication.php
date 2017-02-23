<?php
namespace Ridibooks\Platform\Cms;

use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\Lib\AzureOAuth2Service;
use Silex\Application;
use Silex\Application\TwigTrait;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CmsApplication extends Application
{
	use TwigTrait;

	public function __construct(array $values = [])
	{
		parent::__construct($values);

		$this->registerTwigServiceProvider();
		$this->registerSessionServiceProvider();

		$this->setDefaultErrorHandler();

		$this->connectDefaultControllers();
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
		$this['twig'] = self::share(
			$this->extend(
				'twig',
				function (\Twig_Environment $twig) {
					$globals = array_merge($this->getTwigGlobalVariables(), $this['twig.env.globals']);
					foreach ($globals as $k => $v) {
						$twig->addGlobal($k, $v);
					}

					foreach ($this->getTwigGlobalFilters() as $filter) {
						$twig->addFilter($filter);
					}

					return $twig;
				}
			)
		);

		$this['twig.loader.filesystem'] = self::share(
			$this->extend(
				'twig.loader.filesystem',
				function (\Twig_Loader_Filesystem $loader) {
					$loader->addPath(__DIR__ . '/../../views/');

					return $loader;
				}
			)
		);
	}

	private function getTwigGlobalVariables()
	{
		$globals = [
			'FRONT_URL' => 'http://' . \Config::$DOMAIN,
			'STATIC_URL' => '/admin/static',
			'BOWER_PATH' => '/static/bower_components',

			'MISC_URL' => \Config::$MISC_URL,
			'BANNER_URL' => \Config::$ACTIVE_URL . '/ridibooks_banner/',
			'ACTIVE_URL' => \Config::$ACTIVE_URL,
			'DM_IMAGE_URL' => \Config::$ACTIVE_URL . '/ridibooks_dm/',

			'PHP_SELF' => $_SERVER['PHP_SELF'],
			'REQUEST_URI' => $_SERVER['REQUEST_URI'],

			"HTTP_HOST_LINK" => \Config::$HTTP_HOST_LINK,
			"SSL_HOST_LINK" => \Config::$SSL_HOST_LINK,

			'base_url' => \Config::$DOMAIN
		];

		if (isset($_SESSION['session_user_menu'])) {
			$globals['session_user_menu'] = $_SESSION['session_user_menu'];
		}

		return $globals;
	}

	private function getTwigGlobalFilters()
	{
		return [
			new \Twig_SimpleFilter('strtotime', 'strtotime')
		];
	}

	private function registerSessionServiceProvider()
	{
		$this->register(
			new SessionServiceProvider(),
			[
				'session.storage.handler' => null
			]
		);

		$this['flashes'] = self::share(function () {
			return $this->getFlashBag()->all();
		});
	}

	private function setDefaultErrorHandler()
	{
		$this['debug'] = \Config::$UNDER_DEV;
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

	private function connectDefaultControllers()
	{
		$this->get('/', function (CmsApplication $app) {
			return $app->redirect('/welcome');
		});

		$this->get('/welcome', function (CmsApplication $app) {
			return $app->render('welcome.twig');
		});

		$this->get('/login', function (CmsApplication $app) {
			LoginService::resetSession();

			$azure_config = $app['azure'];
			$end_point = AzureOAuth2Service::getAuthorizeEndPoint($azure_config);
			return $app->render('login.twig', ['azure_login' => $end_point]);
		});

		$this->post('/login', function (Request $req) {
			$id = $req->get('id');
			$passwd = $req->get('passwd');
			$return_url = $req->get('return_url', 'welcome');

			try {
				$login_service = new LoginService();
				$login_service->doLoginAction($id, $passwd);

				return RedirectResponse::create($return_url);
			} catch (\Exception $e) {
				return UrlHelper::printAlertRedirect('/login?return_url=' . urlencode($return_url), $e->getMessage());
			}
		});

		$this->get('/login.azure', function (Request $req, CmsApplication $app) {
			$code = $req->get('code');
			$return_url = $req->get('return_url', 'welcome');

			error_log("return_url=$return_url");

			if (!$code) {
				$error = $req->get('error');
				$error_description = $req->get('error_description');
				return UrlHelper::printAlertRedirect('/login?return_url=' . urlencode($return_url), "$error: $error_description");
			}

			try {
				$azure_config = $app['azure'];
				$login_service = new LoginService();
				$login_service->doAzureLoginAction($code, $azure_config);
				return RedirectResponse::create($return_url);

			} catch (\Exception $e) {
				return UrlHelper::printAlertRedirect('/login?return_url=' . urlencode($return_url), $e->getMessage());
			}
		});

		$this->get('/logout', function () {
			LoginService::resetSession();
			return RedirectResponse::create('/');
		});
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

	public function getFlashBag() : FlashBag
	{
		return $this['session']->getFlashBag();
	}
}
