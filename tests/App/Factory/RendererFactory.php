<?php

namespace App\Factory;

use Zend\View\View;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

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
            'script_paths' => [ROOT.'/Pages'],
        ));
        $phpRenderer = new PhpRenderer;
        $phpRenderer->setResolver($resolver);
        $phpRenderer->setHelperPluginManager($container->get('plugin'));  // Custom plugin manager
        return $phpRenderer;
    }
}        