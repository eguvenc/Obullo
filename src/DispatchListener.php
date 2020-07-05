<?php

namespace Obullo;

use ReflectionClass;
use ReflectionMethod;
use Laminas\Diactoros\Stream;
use Obullo\Error\ErrorHandlerManager;
use Obullo\Router\RouteInterface;
use Obullo\Middleware\DispatchHandler;
use Obullo\View\AbstractPageView;
use Obullo\Exception\InvalidPageResponseException;
use Psr\Http\Message\ResponseInterface;

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
        $this->listeners[] = $events->attach(PageEvent::EVENT_MIDDLEWARES, [$this, 'onMiddlewares']);
        $this->listeners[] = $events->attach(PageEvent::EVENT_DISPATCH_PAGE, [$this, 'onDispatchPage']);
        $this->listeners[] = $events->attach(PageEvent::EVENT_DISPATCH_PARTIAL_PAGE, [$this, 'onDispatchPartialPage']);
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
        $router = $container->get('Router');
        $app = $params['app'];

        if ($router->hasMatch()) {
            foreach ((array)$params['middlewares'] as $appMiddleware) {
                $app->pipe($container->build($appMiddleware));
            }
            $routeMiddlewares = Self::parseRouteMiddlewares($e);
            foreach ($routeMiddlewares as $routeMiddleware) {
                $app->pipe($container->build($routeMiddleware));
            }
            $app->pipe($container->get(DispatchHandler::class));
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
        $handler = $route->getHandler();
        $application  = $e->getApplication();
        $events = $application->getEventManager();
        $container = $application->getContainer();

        $pageModel = new $handler;
        if ($pageModel instanceof AbstractPageView) {
            $pageModel->setView($container->get(View::class));
            $pageModel->setViewPhpRenderer($container->get('ViewPhpRenderer'));
        }
        $e->setPageModel($handler, $pageModel);

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
        $dispatcher->setRequest($request);
        $dispatcher->setPageMethod($methodName);
        $dispatcher->setRouter($container->get('Router'));
        $dispatcher->setPageModel($pageModel);
        $dispatcher->setReflectionClass($reflection);
        $response = $dispatcher->dispatch();
        
        if ($response instanceof Stream) {
            throw new InvalidPageResponseException(
                sprintf(
                    'Return value of %s method must be an instance of Psr\Http\Message\ResponseInterface, Laminas\Diactoros\Stream returned.',
                    $handler.'::onGet'
                )
            );
        }
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
        $handler = $e->getHandler();
        $request = $e->getRequest();
        $application  = $e->getApplication();
        $container = $application->getContainer();

        $pageModel = new $handler;
        if ($pageModel instanceof AbstractPageView) {
            $pageModel->setView($container->get(View::class));
            $pageModel->setViewPhpRenderer($container->get('ViewPhpRenderer'));
        }
        $e->setPageModel($handler, $pageModel);

        $dispatcher = new Dispatcher(['partival_view' => true]);
        $dispatcher->setContainer($container);
        $dispatcher->setRequest($request);
        $dispatcher->setPageMethod('onGet');
        $dispatcher->setRouter($container->get('Router'));
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
