<?php

namespace Obullo\Factory;

use Laminas\View\View;
use Laminas\View\ViewEvent;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ViewFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $renderer = $container->get('ViewPhpRenderer');

        $view = new View;
        $events = $container->get('EventManager');
        $view->setEventManager($events);

        // Attach event listener to add the renderer
        // 
        $view->getEventManager()->attach(ViewEvent::EVENT_RENDERER,
            static function () use ($renderer) {
                return $renderer;
            }
        );
        return $view;
    }
}
