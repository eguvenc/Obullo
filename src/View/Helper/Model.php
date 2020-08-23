<?php

namespace Obullo\View\Helper;

use Obullo\PageEvent;
use Interop\Container\ContainerInterface;
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
     * Set controller to render partial view
     *
     * @param  string     $controller partial model name
     * @param  array|null $options    options
     * @return response
     */
    public function __invoke(string $controller, array $options = null)
    {
        $application = $this->container->get('Application');
        $event = $application->getPageEvent();
        $events = $application->getEventManager();

        $event->setName(PageEvent::EVENT_DISPATCH_PARTIAL_PAGE);
        $event->setController($controller);
        $response = $events->triggerEvent($event)->last();

        return $response;
    }
}
