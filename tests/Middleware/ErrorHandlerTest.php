<?php

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\Response;
use Obullo\Http\ServerRequest;
use Obullo\Middleware\ErrorHandler;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorHandlerTest extends TestCase
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
        $this->container->addAbstractFactory(new Obullo\Factory\LazyPageFactory);
        $this->container->addAbstractFactory(new Obullo\Factory\LazyMiddlewareFactory);

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
        $this->container->setAllowOverride(true);

        $this->errorHandler = new ErrorHandler(
            $this->container->get('Request'),
            function () {
                return new Response;
            },
            $this->container->get('App\Middleware\ErrorResponseGenerator')
        );
    }

    public function testProcess()
    {
        $response = $this->errorHandler->process(
            $this->container->get('Request'),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );
        $this->assertStringContainsString('Return value of Double\RequestHandlerInterface', $response->getBody());
    }

    public function testAttachListeners()
    {
        $this->errorHandler->attachListener(function ($request, $response) {
            $this->assertStringContainsString('Test Exception', $response->getBody());
        });
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/test_error'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertStringContainsString('Test Exception', $response->getBody());
    }
}
