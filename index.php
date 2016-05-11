<?php
use Ridibooks\Platform\Cms\MiniRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once __DIR__ . '/include/bootstrap_cms.php';

if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
	if (!MiniRouter::selfRouting(__DIR__ . '/controls', __DIR__ . '/views')) {
		return RedirectResponse::create('/welcome')->send();
	}
}
