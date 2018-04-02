<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LogLevel;
use Ridibooks\Cms\Thrift\ThriftServer;

class ThriftServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['thrift.logger'] = null;
        $app['thrift.loglevel'] = LogLevel::INFO;

        $app['thrift.server'] = function ($app) {
            $wrapped_services = array_map(function ($service) use ($app) {
                return new ThriftServiceWrapper($service, $app['thrift.logger'], $app['thrift.loglevel']);
            }, $app['thrift.services']);

            return new ThriftServer($wrapped_services);
        };
    }
}
