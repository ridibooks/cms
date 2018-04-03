<?php
declare(strict_types=1);

namespace Ridibooks\Cms\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Ridibooks\Cms\Lib\AzureOAuth2Service;

class AzureServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['azure'] = function ($app) {
            return new AzureOAuth2Service($app['azure.options']);
        };
    }
}
