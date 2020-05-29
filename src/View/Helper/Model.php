<?php

namespace Obullo\View\Helper;

use Obullo\PageEvent;
use Psr\Container\ContainerInterface;
use Laminas\View\Helper\AbstractHelper;

class Model extends AbstractHelper
{
    private $container;

    /**
     * Set container
     *
     * @param object $container container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set handler class to render partial view
     *
     * @param  string     $handlerClass partial model name
     * @param  array|null $options      options
     * @return response
     */
    public function __invoke($handlerClass, array $options = null)
    {
        $application = $this->container->get('Application');
        $event = $application->getPageEvent();
        $events = $application->getEventManager();

        $event->setName(PageEvent::EVENT_PARTIAL_VIEW);
        $event->setHandler($handlerClass);
        $response = $events->triggerEvent($event)->last();

        return $response;
    }
}
