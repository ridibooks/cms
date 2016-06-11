<?php
use Ridibooks\Platform\Cms\MiniRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/include/bootstrap_cms.php';

$router = new MiniRouter(__DIR__ . '/controls', __DIR__ . '/views');
$response = $router->route(Request::createFromGlobals());
if ($response->isNotFound()) {
	$response = new RedirectResponse('/welcome');
}
$response->send();
