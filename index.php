<?php
use Ridibooks\Library\UrlHelper;
use Ridibooks\Platform\Cms\Auth\AdminAuthService;

require_once __DIR__ . '/include/bootstrap_cms.php';

if (AdminAuthService::isValidLogin()) {
	UrlHelper::redirect('/admin/welcome');
} else {
	UrlHelper::redirect('/admin/login');
}
