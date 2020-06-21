<?php

use PHPUnit\Framework\TestCase;
use Obullo\Error\ErrorHandlerManager;
use Laminas\ServiceManager\ServiceManager;

class ErrorHandlerManagerTest extends TestCase
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

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
        $this->container->setAllowOverride(true);
    }

    public function testGetContainer()
    {
        $errorManager = new ErrorHandlerManager;
        $errorManager->setContainer($this->container);

        $this->assertInstanceOf('Laminas\ServiceManager\ServiceManager', $errorManager->getContainer());
    }

    public function testGetConfig()
    {
        $config = $this->container->get('config');

        $errorManager = new ErrorHandlerManager;
        $errorManager->setConfig($config);
        $translatorFactory = $config['service_manager']['factories']['Laminas\I18n\Translator\TranslatorInterface'];

        $this->assertEquals($translatorFactory, Laminas\I18n\Translator\TranslatorServiceFactory::class);
    }

    public function testGetResolvedModule()
    {
        $errorManager = new ErrorHandlerManager;
        $errorManager->setResolvedModule('Test');

        $this->assertEquals('Test', $errorManager->getResolvedModule());
    }

    public function testGetHandlers()
    {
        $config['error_handlers'] =  array(
            'Test' => [
                'error_404' => 'Test\Middleware\ErrorNotFoundHandler',
                'error_generator' => 'Test\Middleware\ErrorResponseGenerator',
            ],
        );
        $errorManager = new ErrorHandlerManager;
        $errorManager->setConfig($config);
        $errorManager->setContainer($this->container);
        $errorManager->setResolvedModule('Test');
        $handlers = $errorManager->getErrorHandlers();

        $this->assertEquals('Test\Middleware\ErrorNotFoundHandler', get_class($handlers['error_404']));
        $this->assertEquals('Laminas\Stratigility\Middleware\ErrorHandler', get_class($handlers['error_generator']));
    }
}