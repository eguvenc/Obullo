<?php

namespace App\Factory;

use Laminas\View\View;
use Laminas\View\ViewEvent;
use Laminas\View\Renderer\RendererInterface;
use Interop\Container\ContainerInterface;
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
        $renderer = $container->get(RendererInterface::class);

        $view = new View;
        $view->getEventManager()
            ->attach(ViewEvent::EVENT_RENDERER, function () use ($renderer) {
                return $renderer;
            });
        return $view;
    }
}
