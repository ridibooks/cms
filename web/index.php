<?php
declare(strict_types=1);

use Ridibooks\Cms\Thrift\ThriftService;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../vendor/autoload.php";

if (is_readable(__DIR__ . '/../.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__, '/../.env');
    $dotenv->overload();
}

// If hostname has a form of dev domain, set test id.
$request = Request::createFromGlobals();
if (!empty($_ENV['TEST_AUTH_DISABLE'])) {
    $hostname = $request->getHost();
    if (preg_match('/^(cms|admin)\.(\w+)\.dev\.ridi\.io$/', $hostname, $matches)) {
        $_ENV['TEST_ID'] = $matches[2];
    }
}

$cms_rpc_url = $_ENV['CMS_RPC_URL'] ?? '';
if (!empty($cms_rpc_url)) {
    ThriftService::setEndPoint($cms_rpc_url);
}

$config = require __DIR__ . '/../config/config.php';
$app = require __DIR__ . '/../src/app.php';
require __DIR__ . '/../src/controllers.php';

$app->run($request);
