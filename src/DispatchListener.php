<?php

namespace Obullo;

use ReflectionClass;
use ReflectionMethod;
use Laminas\Diactoros\Stream;
use Obullo\View\AbstractView;
use Obullo\Error\ErrorHandlerManager;
use Obullo\Middleware\DispatchHandler;
use Obullo\Exception\InvalidPageResponseException;
use Psr\Http\Message\ResponseInterface;

use Laminas\View\View;
use Laminas\Router\RouteMatch;
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
        $app = $params['app'];

        foreach ((array)$params['middlewares'] as $appMiddleware) {
            $app->pipe($container->build($appMiddleware));
        }
        if ($routeMatch = $e->getRouteMatch()) {
            $routeMiddlewares = Self::parseRouteMiddlewares($routeMatch, $e);
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
        $request = $e->getRequest();
        $controller = $e->getController();
        $application  = $e->getApplication();
        $events = $application->getEventManager();
        $container = $application->getContainer();

        $pageModel = $container->build($controller);
        if ($pageModel instanceof AbstractView) {
            $pageModel->setView($container->get(View::class));
            $pageModel->setViewPhpRenderer($container->get('ViewPhpRenderer'));
            $pageModel->init();
        }
        $e->setPageModel($controller, $pageModel);

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
        $dispatcher->setRequest($request);
        $dispatcher->setPageMethod($methodName);
        $dispatcher->setRouteMatch($e->getRouteMatch());
        $dispatcher->setPageModel($pageModel);
        $dispatcher->setReflectionClass($reflection);
        $response = $dispatcher->dispatch();
        
        if ($response instanceof Stream) {
            throw new InvalidPageResponseException(
                sprintf(
                    'Return value of %s method must be an instance of Psr\Http\Message\ResponseInterface, Laminas\Diactoros\Stream returned.',
                    $controller.'::'.$methodName
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
        $controller = $e->getController();
        $request = $e->getRequest();
        $application  = $e->getApplication();
        $container = $application->getContainer();

        $pageModel = $container->build($controller);
        if ($pageModel instanceof AbstractView) {
            $pageModel->setView($container->get(View::class));
            $pageModel->setViewPhpRenderer($container->get('ViewPhpRenderer'));
            $pageModel->init();
        }
        $e->setPageModel($controller, $pageModel);

        $dispatcher = new Dispatcher(['partival_view' => true]);
        $dispatcher->setRequest($request);
        $dispatcher->setPageMethod('onGet');
        $dispatcher->setRouteMatch($e->getRouteMatch());
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();
    
        return $response;
    }

    /**
     * Parse route middlewares
     *
     * @param  object RouteMatch
     * @param  object PageEvent
     * @return array
     */
    private static function parseRouteMiddlewares(RouteMatch $routeMatch, PageEvent $e) : array
    {
        $routeParams = $routeMatch->getParams();
        $routeMiddlewares = isset($routeParams['middleware']) ? (array)$routeParams['middleware'] : array();

        $middlewares = MiddlewareParser::parse(
            $routeMiddlewares,
            $e->getRequest()->getMethod()
        );
        return $middlewares;
    }
}
