<?php

namespace Ridibooks\Cms\Controller;

use Ridibooks\Cms\Thrift\ThriftServer;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThriftController
{
    public function process(Request $request, Application $app)
    {
        /** @var ThriftServer $thrift */
        $thrift = $app['thrift.server'];
        $output = $thrift->process($request->getContent());

        return new Response($output, Response::HTTP_OK, [
            'Content-Type' => 'application/x-thrift'
        ]);
    }
}
