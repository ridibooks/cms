<?php
declare(strict_types=1);

use JG\Silex\Provider\CapsuleServiceProvider;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\CmsApplication;
use Ridibooks\Cms\Service\LoginService;

// start session
$session_domain = $_ENV['SESSION_DOMAIN'] ?? '';
$couchbase_host = $_ENV['COUCHBASE_HOST'] ?? '';
$memcache_host = $_ENV['MEMCACHE_HOST'] ?? '';

if (!empty($memcache_host)) {
    LoginService::startMemcacheSession($memcache_host, $session_domain);
} elseif (!empty($couchbase_host)) {
    LoginService::startCouchbaseSession($couchbase_host, $session_domain);
} else {
    LoginService::startSession($session_domain);
}

$app = new CmsApplication();
$app->register(new CapsuleServiceProvider());
$app->register(new SentryServiceProvider());

return $app;
