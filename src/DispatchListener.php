<?php

namespace Obullo;

use Obullo\Dispatcher;
use ReflectionMethod;
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
        $this->listeners[] = $events->attach(PageEvent::EVENT_PAGE_VIEW, [$this, 'onPageView']);
        $this->listeners[] = $events->attach(PageEvent::EVENT_PARTIAL_VIEW, [$this, 'onPartialView']);
    }

    /**
     * Resolve page view and dispatch
     *
     * @param  PageEvent $e object
     * @return response
     */
    public function onPartialView(PageEvent $e)
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
     * Resolve page view and dispatch
     *
     * @param  PageEvent $e object
     * @return response
     */
    public function onPageView(PageEvent $e)
    {
        $route = $e->getRouter()->getMatchedRoute();
        $request = $e->getRequest();
        $handlerClass = $route->getHandler();
        $application  = $e->getApplication();
        $events = $application->getEventManager();
        $container = $application->getContainer();

        $pageModel  = $container->build($handlerClass);
        $pageModel->setView($container->get(View::class));
        $pageModel->setViewPhpRenderer($container->get('ViewPhpRenderer'));

        $reflection = $pageModel->getReflection();
        $method = $request->getMethod();
        $queryParams = $request->getQueryParams();

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $classMethod) {
            if (isset($queryParams[$classMethod->name])) {
                $method = substr($classMethod->name, 2);
            }
        }
        // The keyword 'on' prevents access to our private methods 
        // that we use in the page model
        // 
        $methodName = 'on'.ucfirst($method); 

        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($container);
        $dispatcher->setMethod($methodName);
        $dispatcher->setPageModel($pageModel);
        $dispatcher->setReflectionClass($reflection);
        $response = $dispatcher->dispatch();
        
        return $response;
    }
}
