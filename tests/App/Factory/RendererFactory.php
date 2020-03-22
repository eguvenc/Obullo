<?php

namespace App\Factory;

use Laminas\View\View;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\View\HelperPluginManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RendererFactory implements FactoryInterface
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
        $resolver = new TemplatePathStack(array(
            'script_paths' => [ROOT],
        ));
        $phpRenderer = new PhpRenderer;
        $phpRenderer->setResolver($resolver);
        $phpRenderer->setHelperPluginManager($container->get(HelperPluginManager::class));  // Custom plugin manager
        return $phpRenderer;
    }
}