<?php

namespace Obullo\Middleware;

use Obullo\PageEvent;
use Obullo\Container\ContainerAwareInterface;
use Obullo\Container\ContainerAwareTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageHandlerMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Process
     *
     * @param  ServerRequestInterface  $request request
     * @param  RequestHandlerInterface $handler request handler
     *
     * @return object|exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $container = $this->getContainer();
        $application = $container->get('Application');
        $event = $application->getPageEvent();
        $events = $application->getEventManager();

        $event->setName(PageEvent::EVENT_DISPATCH_PAGE);
        $event->setRequest($request);
        $response = $events->triggerEvent($event)->last();

        if ($response instanceof ResponseInterface) {
            return $response;
        }
        return $handler->handle($request);
    }
}
