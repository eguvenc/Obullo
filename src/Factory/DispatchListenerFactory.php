<?php

namespace Obullo\Factory;

use Psr\Container\ContainerInterface;
use Obullo\DispatchListener;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DispatchListenerFactory implements FactoryInterface
{
    /**
     * Create the default dispatch listener.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return DispatchListener
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new DispatchListener;
    }
}
