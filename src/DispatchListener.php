<?php

namespace Obullo;

use ReflectionClass;
use ReflectionMethod;

use Obullo\Dispatcher;
use Obullo\Router\RouteInterface;
use Obullo\Middleware\PageHandlerMiddleware;

use Laminas\View\View;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class DispatchListener extends AbstractListenerAggregate
{
    /**
     * Attach listeners to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(PageEvent::EVENT_ERROR_HANDLERS, [$this, 'onErrorHandlers']);
        $this->listeners[] = $events->attach(PageEvent::EVENT_MIDDLEWARES, [$this, 'onMiddlewares']);
        $this->listeners[] = $events->attach(PageEvent::EVENT_DISPATCH_PAGE, [$this, 'onDispatchPage']);
        $this->listeners[] = $events->attach(PageEvent::EVENT_DISPATCH_PARTIAL_PAGE, [$this, 'onDispatchPartialPage']);
    }

    /**
     * Dispatch error handlers
     *
     * Returns to configured error handlers of current module,
     * if there is no configuration App module handlers are the default
     *
     * @param  PageEvent $e object
     * @return array
     */
    public function onErrorHandlers(PageEvent $e) : array
    {
        $application = $e->getApplication();

        $errorManager = new ErrorHandlerManager;
        $errorManager->setConfig($application->getConfig());
        $errorManager->setContainer($application->getContainer());
        $errorManager->setResolvedModule($e->getResolvedModuleName()); // App, Blog, Forum etc..

        return $errorManager->getErrorHandlers();
    }

    /**
     * Dispatch route middlewares
     *
     * @param  PageEvent $e object
     * @return void
     */
    public function onMiddlewares(PageEvent $e)
    {
        $application = $e->getApplication();
        $container = $application->getContainer();
        $params = $e->getParams();
        $app = $params['app'];

        // check route match & assign middlewares
        //
        if ($params['route_result'] instanceof RouteInterface) {
            foreach ((array)$params['middlewares'] as $appMiddleware) {
                $app->pipe($container->build($appMiddleware));
            }
            $routeMiddlewares = Self::parseRouteMiddlewares($e);
            foreach ($routeMiddlewares as $routeMiddleware) {
                $app->pipe($container->build($routeMiddleware));
            }
            $app->pipe($container->get(PageHandlerMiddleware::class));
        }
    }

    /**
     * Dispatch page
     *
     * @param  PageEvent $e object
     * @return response
     */
    public function onDispatchPage(PageEvent $e)
    {
        $route = $e->getRouter()->getMatchedRoute();
        $request = $e->getRequest();
        $handlerClass = $route->getHandler();
        $application  = $e->getApplication();
        $events = $application->getEventManager();
        $container = $application->getContainer();

        $pageModel = $container->build($handlerClass);
        $pageModel->setView($container->get(View::class));
        $pageModel->setViewPhpRenderer($container->get('ViewPhpRenderer'));

        $reflection = new ReflectionClass($pageModel);
        $method = $request->getMethod();
        $queryParams = $request->getQueryParams();

        $queryMethod = null;
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $classMethod) {
            $name = $classMethod->getName();
            if (array_key_exists($name, $queryParams)) {  // check query method is available
                $method = substr($name, 2);
                $queryMethod = 'on'.ucfirst($method);
            }
        }
        // the keyword 'on' prevents access to our special methods
        // that we use in the page model
        //
        $methodName = 'on'.ucfirst($method);

        // let's inject query method name to page model
        //
        if (method_exists($pageModel, 'setQueryMethod')) {
            $pageModel->setQueryMethod($queryMethod);
        }
        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($container);
        $dispatcher->setMethod($methodName);
        $dispatcher->setPageModel($pageModel);
        $dispatcher->setReflectionClass($reflection);
        $response = $dispatcher->dispatch();
        
        return $response;
    }

    /**
     * Dispatch partial page
     *
     * @param  PageEvent $e object
     * @return response
     */
    public function onDispatchPartialPage(PageEvent $e)
    {
        $handlerClass = $e->getHandler();
        $application  = $e->getApplication();
        $container = $application->getContainer();

        $pageModel = $container->build($handlerClass);
        $pageModel->setView($container->get(View::class));
        $pageModel->setViewPhpRenderer($container->get('ViewPhpRenderer'));

        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($container);
        $dispatcher->setMethod('onGet');
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();
        
        return $response;
    }

    /**
     * Parse route middlewares
     *
     * @param  object $router Router
     * @return array
     */
    private static function parseRouteMiddlewares(PageEvent $e) : array
    {
        $middlewares = MiddlewareParser::parse(
            $e->getRouter()->getMiddlewares(),
            $e->getRequest()->getMethod()
        );
        return $middlewares;
    }
}
