<?php

namespace Obullo\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ModuleManager\Listener\ListenerOptions;  
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ServiceManager\Factory\FactoryInterface;

use Obullo\ModuleManager\Listener\DefaultListenerAggregate;

class ModuleManagerFactory implements FactoryInterface
{
    /**
     * Creates and returns the module manager
     *
     * Instantiates the default module listeners, providing them configuration
     * from the "module_listener_options" key of the ApplicationConfig
     * service. Also sets the default config glob path.
     *
     * Module manager is instantiated and provided with an EventManager, to which
     * the default listener aggregate is attached. The ModuleEvent is also created
     * and attached to the module manager.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ModuleManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $configuration = $container->get('appConfig');
        $serviceListener = $container->get('ServiceListener');

        // https://github.com/laminas/laminas-mvc/blob/master/src/Service/ModuleManagerFactory.php
        //
        $serviceListener->addServiceManager(
            $container,
            'service_manager',
            'Laminas\ModuleManager\Feature\ServiceProviderInterface',
            'getServiceConfig'
        );
        $serviceListener->addServiceManager(
            'ViewHelperManager',
            'view_helpers',
            'Laminas\ModuleManager\Feature\ViewHelperProviderInterface',
            'getViewHelperConfig'
        );
        $events = $container->get('EventManager');
        $listenerOptions  = new ListenerOptions($configuration['module_listener_options']);
        $defaultListeners = new DefaultListenerAggregate($listenerOptions);

        $defaultListeners->attach($events);
        $serviceListener->attach($events);

        $moduleEvent = new ModuleEvent;
        $moduleEvent->setParam('ServiceManager', $container);

        $moduleManager = new ModuleManager($configuration['modules'], $events);
        $moduleManager->setEvent($moduleEvent);

        return $moduleManager;
    }
}