<?php

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\Response;
use Obullo\Http\ServerRequest;
use Obullo\Middleware\NotFoundHandler;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundHandlerTest extends TestCase
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

        $this->notFoundHandler = new NotFoundHandler(
            function () {
                return new Response;
            },
            $this->container->get('App\Middleware\NotFoundResponseGenerator')
        );
    }

    public function testProcess()
    {
        $response = $this->notFoundHandler->process(
            $this->container->get('Request'),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );
        $this->assertStringContainsString('Page Not Found', $response->getBody());
    }

    public function testAttachListeners()
    {
        $this->notFoundHandler->attachListener(function ($request, $response) {
            $this->assertStringContainsString('Page Not Found', $response->getBody());
        });
        $response = $this->notFoundHandler->process(
            $this->container->get('Request'),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );
        $this->assertStringContainsString('Page Not Found', $response->getBody());
    }
}
