<?php
declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

if (is_readable(__DIR__ . '/../.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__, '/../.env');
    $dotenv->overload();
}

$app = require __DIR__ . '/../src/app.php';
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../src/controllers.php';

$app->run();
