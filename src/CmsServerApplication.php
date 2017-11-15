<?php
namespace Ridibooks\Cms;

use JG\Silex\Provider\CapsuleServiceProvider;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\Thrift\ThriftResponse;
use Silex\Application\TwigTrait;
use Symfony\Component\HttpFoundation\Request;

class CmsServerApplication extends CmsApplication
{
    use TwigTrait;

    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $this['twig.path'] = __DIR__ . '/../views/';

        $this->registerCapsuleService();
        $this->registerSentryServiceProvider();

        // thrift proxy
        $this->post('/', function (Request $request) {
            return ThriftResponse::create($request);
        });

        // web server
        $this->mount('/', new LoginController());
        $this->mount('/', new MyInfoController());
        $this->mount('/', new CommonController());
    }

    private function registerCapsuleService()
    {
        $this->register(
            new CapsuleServiceProvider(),
            [
                'capsule.connections' => [
                    'default' => [
                        'driver' => 'mysql',
                        'host' => $_ENV['MYSQL_HOST'],
                        'database' => $_ENV['MYSQL_DATABASE'],
                        'username' => $_ENV['MYSQL_USER'],
                        'password' => $_ENV['MYSQL_PASSWORD'],
                        'charset' => 'utf8',
                        'collation' => 'utf8_unicode_ci'
                    ]
                ],
                'capsule.options' => [
                    'setAsGlobal' => true,
                    'bootEloquent' => true,
                ],
            ]
        );
    }

    private function registerSentryServiceProvider()
    {
        if (empty($_ENV['SENTRY_KEY'])) {
            return;
        }

        $this->register(new SentryServiceProvider(), [
            SentryServiceProvider::SENTRY_OPTIONS => [
                SentryServiceProvider::OPT_DSN => $_ENV['SENTRY_KEY'],
            ]
        ]);

        $client = $this[SentryServiceProvider::SENTRY];
        $client->install();
    }
}
