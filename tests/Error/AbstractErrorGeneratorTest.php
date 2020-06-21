<?php

use PHPUnit\Framework\TestCase;
use Obullo\Error\AbstractErrorGenerator;
use Laminas\ServiceManager\ServiceManager;

class AbstractErrorGeneratorTest extends TestCase
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

        $this->errorGenerator = $this->getMockForAbstractClass(
            AbstractErrorGenerator::class,
            [$this->container]
        );
    }

    public function testGetContainer()
    {
        $this->assertInstanceOf('Laminas\ServiceManager\ServiceManager', $this->errorGenerator->getContainer());
    }

    public function testGetDevelopmentMode()
    {
        $this->assertTrue($this->errorGenerator->getDevelopmentMode());
    }

    public function testGetModuleName()
    {
        $this->assertEquals('App', $this->errorGenerator->getModuleName());
    }

    public function testGetViewModel()
    {
        $this->errorGenerator->triggerErrorEvent();

        $this->assertInstanceOf('Laminas\View\Model\ViewModel', $this->errorGenerator->getViewModel());
    }

    public function testErrorTemplateForExceptions()
    {
        $this->errorGenerator->triggerErrorEvent();

        $this->assertInstanceOf('Laminas\View\Model\ViewModel', $this->errorGenerator->getViewModel());
        $this->assertEquals('App\Pages\Templates\ErrorsAndExceptions', $this->errorGenerator->getViewModel()->getTemplate());
    }

    public function testErrorTemplateFor404NotFound()
    {
        $this->errorGenerator->trigger404Event();

        $this->assertInstanceOf('Laminas\View\Model\ViewModel', $this->errorGenerator->getViewModel());
        $this->assertEquals('App\Pages\Templates\ErrorNotFound', $this->errorGenerator->getViewModel()->getTemplate());
    }

    public function testRenderErrorsAndExceptions()
    {
        $this->errorGenerator->triggerErrorEvent();
        $html = $this->errorGenerator->render($this->errorGenerator->getViewModel());
        $this->assertStringContainsString('An error was encountered', $html);

        $exception = new ErrorException('Exception error', 0, 0, 0, 0);
        $this->errorGenerator->triggerErrorEvent($exception);
        $html = $this->errorGenerator->render($this->errorGenerator->getViewModel());
    
        $this->assertStringContainsString('Exception error', $html);
    }

    public function testRenderExceptionsWhenDevelopmentModeOff()
    {
        $config = $this->container->get('config');
        $config['view_manager']['display_exceptions'] = false;
        $this->container->setService('config', $config);
        $this->errorGenerator->setDevelopmentMode();
        
        $exception = new ErrorException('Exception error', 0, 0, 0, 0);
        $this->errorGenerator->triggerErrorEvent($exception);
        $html = $this->errorGenerator->render($this->errorGenerator->getViewModel());
    
        $this->assertStringContainsString('An error was encountered', $html);
    }

    public function testRenderErrorNotFound()
    {
        $this->errorGenerator->trigger404Event();
        $html = $this->errorGenerator->render($this->errorGenerator->getViewModel());

        $this->assertStringContainsString('The page you are looking for could not be found', $html);
    }

    public function testGetPageEvent()
    {
        $this->errorGenerator->trigger404Event();

        $this->assertInstanceOf('Obullo\PageEvent', $this->errorGenerator->getPageEvent());
    }
}
