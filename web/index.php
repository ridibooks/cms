<?php
declare(strict_types=1);

use Ridibooks\Cms\Thrift\ThriftService;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../vendor/autoload.php";

if (is_readable(__DIR__ . '/../.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__, '/../.env');
    $dotenv->overload();
}

$config = require __DIR__ . '/../config/config.php';
$app = require __DIR__ . '/../src/app.php';
require __DIR__ . '/../src/controllers.php';

$app->run(Request::createFromGlobals());
