<?php

namespace Ridibooks\Cms\Lib;

use Ridibooks\Cms\Auth\AdminAuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareFactory
{
    public static function authRequired(): callable
    {
        return function (Request $request) {
            return AdminAuthService::authorize($request);
        };
    }

    public static function thriftContent(): callable
    {
        return function (Request $request) {
            $request->setFormat('thrift', 'application/x-thrift');

            $content_type = $request->getContentType();
            if ($content_type !== 'thrift') {
                return new Response('Thrift request is only acceptable', Response::HTTP_BAD_REQUEST);
            }

            return null;
        };
    }
}
