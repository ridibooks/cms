<?php
use Ridibooks\Cms\CmsServerApplication;
use Ridibooks\Cms\MiniRouter;
use Ridibooks\Cms\Service\LoginService;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require __DIR__ . "/vendor/autoload.php";

$dotenv = new Dotenv\Dotenv(__DIR__, '.env');
$dotenv->load();

// start session
$session_domain = $_ENV['SESSION_DOMAIN'];
$couchbase_host = $_ENV['COUCHBASE_HOST'];
if (isset($couchbase_host) && $couchbase_host !== '') {
	LoginService::startCouchbaseSession($couchbase_host, $session_domain);
} else {
	LoginService::startSession($session_domain);
}

$app = new CmsServerApplication([
    'debug' => $_ENV['DEBUG'],
	'sentry_key' => $_ENV['SENTRY_KEY'],
    'mysql' => [
        'host' => $_ENV['MYSQL_HOST'],
        'database' => $_ENV['MYSQL_DATABASE'],
        'user' => $_ENV['MYSQL_USER'],
        'password' => $_ENV['MYSQL_PASSWORD'],
    ],
    'azure' => [
        'tenent' => $_ENV['AZURE_TENENT'],
        'client_id' => $_ENV['AZURE_CLIENT_ID'],
        'client_secret' => $_ENV['AZURE_CLIENT_SECRET'],
        'resource' => $_ENV['AZURE_RESOURCE'],
        'redirect_uri' => $_ENV['AZURE_REDIRECT_URI'],
        'api_version' => $_ENV['AZURE_API_VERSION'],
    ],
]);

// check auth
$app->before(function (Request $request) {
	return MiniRouter::shouldRedirectForLogin($request);
});

$app->run();
