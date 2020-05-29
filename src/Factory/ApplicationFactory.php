<?php

namespace Obullo\Factory;

use Psr\Container\ContainerInterface;
use Obullo\Application;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ApplicationFactory implements FactoryInterface
{
    /**
     * Create the Application service
     *
     * Creates a Obullo\Application service, passing it the configuration
     * service and the service manager instance.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new Application(
            $container,
            $container->get('EventManager'),
            $container->get('Request'),
            $container->get('Router')
        );
    }
}
