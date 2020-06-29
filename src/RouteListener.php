<?php

namespace Obullo;

use Obullo\PageEvent;
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
        $router  = $event->getApplication()->getContainer()->get('Router');
        $route   = $router->matchRequest();
        if (false == $route) {
            return false;
        }
        $event->setRouter($router);
        $event->setMatchedRoute($route);
        $event->setHandler($route->getHandler());
        $event->setResolvedModuleName();
    }
}
