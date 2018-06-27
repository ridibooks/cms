<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LogLevel;
use Ridibooks\Cms\Thrift\ThriftServer;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThriftServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['thrift.logger'] = null;
        $app['thrift.loglevel'] = LogLevel::INFO;

        $app['thrift.end_point'] = '/';

        $app['thrift.acceptable_checker'] = function () {
            return function (Request $request) {
                $request->setFormat('thrift', 'application/x-thrift');
                $content_type = $request->getContentType();
                if ($content_type !== 'thrift') {
                    return new Response('Thrift request is only acceptable', Response::HTTP_BAD_REQUEST);
                }

                return null;
            };
        };

        $app['thrift.processor'] = function () {
            return function (Request $request, Application $app) {
                $output = $app['thrift.server']->process($request->getContent());
                return new Response($output, Response::HTTP_OK, [
                    'Content-Type' => 'application/x-thrift'
                ]);
            };
        };

        $app['thrift.server'] = function (Container $app) {
            $wrapped_services = array_map(function ($service) use ($app) {
                return new ThriftServiceWrapper($service, $app['thrift.logger'], $app['thrift.loglevel']);
            }, $app['thrift.services']);

            return new ThriftServer($wrapped_services);
        };
    }

    public function boot(Application $app)
    {
        $app->post($app['thrift.end_point'], $app['thrift.processor'])
            ->before($app['thrift.acceptable_checker']);
    }
}
