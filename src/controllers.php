<?php
declare(strict_types=1);

use Ridibooks\Cms\Controller\CommonController;
use Ridibooks\Cms\Controller\LoginController;
use Ridibooks\Cms\Controller\MyInfoController;
use Ridibooks\Cms\MiniRouter;
use Ridibooks\Cms\Thrift\ThriftResponse;
use Symfony\Component\HttpFoundation\Request;

$app->post('/', function (Request $request) {
    return ThriftResponse::create($request);
});

// web server
$app->mount('/', new CommonController());
$app->mount('/', new MyInfoController());
$app->mount('/', new LoginController());

// check auth
$app->before(function (Request $request) {
    return MiniRouter::shouldRedirectForLogin($request);
});
