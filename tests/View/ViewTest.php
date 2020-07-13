<?php

use PHPUnit\Framework\TestCase;
use Obullo\PageEvent;
use Obullo\View\View;
use Laminas\Diactoros\Uri;
use Obullo\Http\ServerRequest;
use Laminas\ServiceManager\ServiceManager;

class ViewTest extends TestCase
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

    public function testPageTemplateName()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/test_view'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('App/Pages/TestView', $response->getBody());
    }

    public function testPageLayout()
    {
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test_view'),
            $method = null,
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = ['onPageLayout' => null],
            $parsedBody = null,
            $protocol = '1.1'
        );
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();
        
        $this->assertEquals('App/Pages/Templates/DefaultLayout', $response->getBody());
    }

    public function testMethodQuery()
    {
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test_view'),
            $method = null,
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = ['onMethodQuery' => null],
            $parsedBody = null,
            $protocol = '1.1'
        );
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('App/Pages/MethodQuery', $response->getBody());
    }

    public function testPlugin()
    {
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test_view'),
            $method = null,
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = ['onPlugin' => null],
            $parsedBody = null,
            $protocol = '1.1'
        );
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('/test_view', $response->getBody());
    }

    public function testModel()
    {
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test_view'),
            $method = null,
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = ['onModel' => null],
            $parsedBody = null,
            $protocol = '1.1'
        );
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('Header', $response->getBody());
    }

    public function testGetViewModel()
    {
        $view = new View;
        
        $this->assertInstanceOf('Laminas\View\Model\ModelInterface', $view->getViewModel());
    }
}