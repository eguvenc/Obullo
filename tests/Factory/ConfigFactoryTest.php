<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

use Laminas\Config\Config;
use Laminas\ModuleManager\ModuleManager;

class ConfigFactoryTest extends TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__.'/../config/application.config.php';

        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->setFactory(Config::class, 'Obullo\Factory\ConfigFactory');
        $this->container->setFactory('ModuleManager', 'Obullo\Factory\ModuleManagerFactory');
        $this->container->setFactory(ModuleManager::class, 'Obullo\Factory\ModuleManagerFactory');
    }

    public function testFactory()
    {
        $config = $this->container->get(Config::class);

        $translatorFactory = $config['service_manager']['factories']['Laminas\I18n\Translator\TranslatorInterface'];

        $this->assertEquals($translatorFactory, Laminas\I18n\Translator\TranslatorServiceFactory::class);
    }
}
