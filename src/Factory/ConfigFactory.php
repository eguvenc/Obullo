<?php

namespace Obullo\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConfigFactory implements FactoryInterface
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
        $moduleManager = $container->get(ModuleManager::class);
        $moduleManager->loadModules();
        $moduleParams = $moduleManager->getEvent()->getParams();
        
        return $moduleParams['configListener']->getMergedConfig(false);
    }
}