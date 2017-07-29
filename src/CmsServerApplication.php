<?php
namespace Ridibooks\Cms;

use JG\Silex\Provider\CapsuleServiceProvider;
use Moriony\Silex\Provider\SentryServiceProvider;
use Ridibooks\Cms\Thrift\ThriftResponse;
use Ridibooks\Platform\Cms\CmsApplication;
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
        $mysql = $this['mysql'];

        $this->register(
            new CapsuleServiceProvider(),
            [
                'capsule.connections' => [
                    'default' => [
                        'driver' => 'mysql',
                        'host' => $mysql['host'],
                        'database' => $mysql['database'],
                        'username' => $mysql['user'],
                        'password' => $mysql['password'],
                        'charset' => 'utf8',
                        'collation' => 'utf8_unicode_ci',
                        'prefix' => '',
                        'options' => [
                            // mysqlnd 5.0.12-dev - 20150407 에서 PDO->prepare 가 매우 느린 현상
                            \PDO::ATTR_EMULATE_PREPARES => true
                        ]
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
        $sentry_dsn = $this['sentry_key'];
        if (isset($sentry_dsn) && $sentry_dsn !== '') {
            $this->register(new SentryServiceProvider(), [
                SentryServiceProvider::SENTRY_OPTIONS => [
                    SentryServiceProvider::OPT_DSN => $sentry_dsn,
                ]
            ]);

            $client = $this[SentryServiceProvider::SENTRY];
            $client->install();
        }
    }
}
