<?php
require_once(__DIR__ . "/../../../include/config.php");

// PSR-4 autoload
$autoloader = require __DIR__ . "/../../../vendor/autoload.php";
$autoloader->add('Ridibooks', __DIR__ . '/../cp/src');

ini_set('max_execution_time', 300);
ini_set('max_input_time', 60);

//Load Local Config(config.local.php)
//
// [Example]
// class Config extends ConfigDefault
// {
// 	static $DB_NAME = "bom2";
// }
//
class AdminConfigDefault
{
	public static $SENTRY_KEY = 'https://ad7985a08242488fb24523ab2ef07193:a5f41ae7c0c94e4bb18c560123dbca68@app.getsentry.com/24382';
	//권한 사용할 것인지
	public static $USE_AUTH = true;
}

if (is_file(__DIR__ . '/admin.config.local.php')) {
	require_once __DIR__ . '/admin.config.local.php';
} else {
	class AdminConfig extends AdminConfigDefault
	{
	}
}
