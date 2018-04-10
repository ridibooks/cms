<?php
declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__, '.env');
$dotenv->overload();
$dotenv->required([
    'MYSQL_HOST',
    'MYSQL_DATABASE',
    'MYSQL_USER',
    'MYSQL_PASSWORD',
]);

$capsule = new Capsule();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['MYSQL_HOST'],
    'database' => $_ENV['MYSQL_DATABASE'],
    'username' => $_ENV['MYSQL_USER'],
    'password' => $_ENV['MYSQL_PASSWORD'],
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
]);

// Set the event dispatcher used by Eloquent models... (optional)
$capsule->setEventDispatcher(new Dispatcher(new Container()));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
