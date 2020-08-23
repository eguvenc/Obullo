<?php

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Uri;
use Obullo\Http\ServerRequest;
use Laminas\ServiceManager\ServiceManager;

class SetLocaleMiddlewareTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__ . '/../config/application.config.php';
        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);

        // setup service manager
        //
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->addAbstractFactory(new Obullo\Factory\LazyMiddlewareFactory);

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
        $this->container->setAllowOverride(true);
    }

    public function testSetLocale()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://es.example.com/test_set_locale'));
        $this->container->setService('Request', $request);
        $config = $this->container->get('config');

        $config['translator'] = [
            'locale' => 'en',
            'translation_file_patterns' => [
                [
                    'type'     => 'phparray',
                    'base_dir' => $config['root'].'/data/language',
                    'pattern'  => '%s/messages.php',
                ],
            ],
            'allowed_languages' => ['en','de','es','fr','tr'],
        ];
        $this->container->setService('config', $config);
        $this->container->setService('Config', $config);
        $this->container->setService(Laminas\Config\Config::class, $config);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('es', $response->getBody());
    }
}
