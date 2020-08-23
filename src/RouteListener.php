<?php

namespace Obullo;

use Obullo\PageEvent;
use Laminas\Router\RouteMatch;
use Laminas\Psr7Bridge\Psr7ServerRequest;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class RouteListener extends AbstractListenerAggregate
{
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(PageEvent::EVENT_ROUTE, [$this, 'onRoute']);
    }

    /**
     * Listen to the "route" event and attempt to route the request
     *
     * @param  PageEvent $event
     * @return null|Obullo\Router\Route
     */
    public function onRoute(PageEvent $event)
    {
        $request = $event->getRequest();
        $router  = $event->getRouter();

        // https://docs.laminas.dev/laminas-psr7bridge/usage-examples/
        //
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));
        $container = $event->getApplication()->getServiceManager();
        $container->setService(RouteMatch::class, $routeMatch);

        if ($routeMatch instanceof RouteMatch) {
            $params = $routeMatch->getParams();
            $event->setRouteMatch($routeMatch);
            $event->setRouter($router);
            $event->setController($params['controller']);
            $event->setResolvedModuleName();
            return $routeMatch;
        }
        return $event->getParams();
    }
}
