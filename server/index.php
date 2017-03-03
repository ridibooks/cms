<?php
use Ridibooks\Cms\Server\CmsServerApplication;

$autoloader = require __DIR__ . "/vendor/autoload.php";

$dotenv = new Dotenv\Dotenv(__DIR__, '.env');
$dotenv->load();


$app = new CmsServerApplication([
	'debug' => true,
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

$app->run();
