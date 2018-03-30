<?php
declare(strict_types=1);

use Ridibooks\Cms\Controller\CommonController;
use Ridibooks\Cms\Controller\LoginController;
use Ridibooks\Cms\Lib\MiddlewareFactory;
use Symfony\Component\HttpFoundation\Response;

// Thrift service
$app->post('/', function () {
    return new Response('Thrift request is only acceptable', Response::HTTP_BAD_REQUEST);
})
->before(MiddlewareFactory::thriftProcessor());

// web service
$app->mount('/', new LoginController());
$app->mount('/', new CommonController());
