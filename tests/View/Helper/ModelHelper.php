<?php

use PHPUnit\Framework\TestCase;
use Obullo\Router\Router;
use Obullo\View\Helper\Model;
use Obullo\Middleware\PageHandlerMiddleware;
use Obullo\Container\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

class ModelHelperTest extends TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__.'/config/application.config.php';
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
    }

    public function testModel()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/test'));
        $this->container->setService('request', $request);

        $router = $this->container->get(Router::class);
        $router->matchRequest();

        $app = new MiddlewarePipe;
        $middleware = $this->container->get(PageHandlerMiddleware::class);
        $app->pipe($middleware);

        $callback = [$app, 'handle'];
        $response = $callback($request, new Response);

        echo $response->getBody();

        die;

        // // initialize to application
        // // 
        // $application = $this->container->get('Application');
        // $application->bootstrap();
        // // $application->run();



        // $plugin = $this->container->get('ViewPhpRenderer')->getHelperPluginManager();
        // $viewModel = $plugin->get('view_model');
        // $viewModel->setRoot($model);

        // $instance = $viewModel('App\Pages\TestModel');
        
        // $this->assertInstanceOf('App\Pages\TestModel', $instance);
    }
}
