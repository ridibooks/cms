<?php
namespace Ridibooks\Platform\Cms;

use Silex\Application;
use Silex\Application\TwigTrait;
use Silex\Provider\TwigServiceProvider;

class CmsApplication extends Application
{
	use TwigTrait;

	public function __construct(array $values = [])
	{
		parent::__construct($values);

		$this->registerTwigServiceProvider();
	}

	private function registerTwigServiceProvider()
	{
		$this->register(
			new TwigServiceProvider(),
			[
				'twig.env.globals' => [],
				'twig.options' => [
					'cache' => sys_get_temp_dir() . '/twig_cache_v12',
					'auto_reload' => true
				]
			]
		);

		// see http://silex.sensiolabs.org/doc/providers/twig.html#customization
		$this['twig'] = $this->share(
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

		$this['twig.loader.filesystem'] = $this->share(
			$this->extend(
				'twig.loader.filesystem',
				function (\Twig_Loader_Filesystem $loader) {
					$loader->addPath(__DIR__ . '/../../../../views/');

					return $loader;
				}
			)
		);
	}

	private function getTwigGlobalVariables()
	{
		return [
			'FRONT_URL' => 'http://' . \Config::$DOMAIN,
			'STATIC_URL' => '/admin/static',
			'MISC_URL' => \Config::$MISC_URL,
			'BANNER_URL' => \Config::$ACTIVE_URL . '/ridibooks_banner/',
			'ACTIVE_URL' => \Config::$ACTIVE_URL,
			'DM_IMAGE_URL' => \Config::$ACTIVE_URL . '/ridibooks_dm/',

			'PHP_SELF' => $_SERVER['PHP_SELF'],
			'REQUEST_URI' => $_SERVER['REQUEST_URI'],

			"HTTP_HOST_LINK" => \Config::$HTTP_HOST_LINK,
			"SSL_HOST_LINK" => \Config::$SSL_HOST_LINK,

			'base_url' => \Config::$DOMAIN,
			'session_user_menu' => $_SESSION['session_user_menu']
		];
	}

	private function getTwigGlobalFilters()
	{
		return [
			new \Twig_SimpleFilter('strtotime', 'strtotime')
		];
	}
}
