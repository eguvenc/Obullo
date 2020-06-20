<?php

namespace Obullo\Factory;

use Laminas\View\View;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ViewPhpRendererFactory implements FactoryInterface
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
        $appConfig = $container->get('appConfig');

        $resolver = new TemplatePathStack(array(
            'script_paths' => [$appConfig['root'].'/src'],
        ));
        $phpRenderer = new PhpRenderer;
        $phpRenderer->setResolver($resolver);
        $phpRenderer->setHelperPluginManager($container->get('ViewHelperManager'));  // Custom plugin manager
        return $phpRenderer;
    }
}