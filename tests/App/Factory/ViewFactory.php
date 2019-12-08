<?php

namespace App\Factory;

use Zend\View\View;
use Zend\View\ViewEvent;
use Zend\View\Renderer\RendererInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

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
