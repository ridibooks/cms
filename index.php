<?php
use Ridibooks\Cms\CmsServerApplication;
use Ridibooks\Cms\Service\LoginService;

$autoloader = require __DIR__ . "/vendor/autoload.php";

$dotenv = new Dotenv\Dotenv(__DIR__, '.env');
$dotenv->load();

if (isset($app['couchbase']) && $app['couchbase'] !== '') {
	$couchbase = $app['couchbase'];
	LoginService::startCouchbaseSession($couchbase['host'], 'admin.ridibooks.com');
} else {
	LoginService::startSession('admin.ridibooks.com');
}

$app = new CmsServerApplication([
    'debug' => $_ENV['DEBUG'],
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
    ]
]);

if (isset($_ENV['COUCHBASE_HOST']) && $_ENV['COUCHBASE_HOST']!=='') {
    $app['couchbase'] = [
        'host' => $_ENV['COUCHBASE_HOST'],
    ];
}

//need auth service

$app->run();
