<?php
use Ridibooks\Library\DB\ConnectionProvider;

$capsule = new Illuminate\Database\Capsule\Manager();

$params = \Config::getConnectionParams(ConnectionProvider::CONNECTION_GROUP_PLATFORM_WRITE);
$capsule->addConnection([
'driver' => 'mysql',
    'host' => $params['host'],
    'database' => 'bom',
    'username' => $params['user'],
    'password' => $params['password'],
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
    'options' => [
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
