<?php

namespace Obullo;

use Obullo\Dispatcher;
use ReflectionClass;
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
}
