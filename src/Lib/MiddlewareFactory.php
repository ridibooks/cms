<?php

namespace Ridibooks\Cms\Lib;

use Ridibooks\Cms\Auth\AdminAuthService;
use Ridibooks\Cms\Thrift\ThriftServer;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareFactory
{
    const THRIFT_RESULT = 'thrift_result';

    public static function loginRequired(): callable
    {
        return function (Request $request) {
            return AdminAuthService::authorize($request);
        };
    }

    public static function thriftProcessor(): callable
    {
        return function (Request $request, Application $app) {
            $request->setFormat('thrift', 'application/x-thrift');
            $content_type = $request->getContentType();

            if ($content_type !== 'thrift') {
                return null;
            }

            /** @var ThriftServer $thrift */
            $thrift = $app['thrift.server'];
            $output = $thrift->process($request->getContent());

            return new Response($output, Response::HTTP_OK, [
                'Content-Type' => 'application/x-thrift'
            ]);
        };
    }
}
