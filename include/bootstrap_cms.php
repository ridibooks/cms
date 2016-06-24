<?php
require_once __DIR__ . '/../../../include/config.php';

// PSR-4 autoload
$autoloader = require __DIR__ . "/../vendor/autoload.php";

$capsule = new Illuminate\Database\Capsule\Manager();

$capsule->addConnection([
	'driver'    => 'mysql',
	'host'      => \Config::$DB_HOST,
	'database'  => 'bom',
	'username'  => \Config::$DB_USER,
	'password'  => \Config::$DB_PASSWD,
	'charset'   => 'utf8',
	'collation' => 'utf8_unicode_ci',
	'prefix'    => '',
	'options'   => [
		// mysqlnd 5.0.12-dev - 20150407 에서 PDO->prepare 가 매우 느린 현상
		PDO::ATTR_EMULATE_PREPARES => true
	]
]);

$capsule->setAsGlobal();

$capsule->bootEloquent();


ini_set('max_execution_time', 300);
ini_set('max_input_time', 60);

mb_internal_encoding('UTF-8');
mb_regex_encoding("UTF-8");

session_set_cookie_params(60 * 60 * 24 * 30, '/', Config::$ADMIN_DOMAIN);
session_start();
