<?php

namespace App\Factory;

use Obullo\View\Helper as Plugin;
use Zend\I18n\View\Helper as ZendPlugin;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\I18n\View\Helper;
use Zend\View\HelperPluginManager;

class PluginManagerFactory implements FactoryInterface
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
        $config = [
            'aliases' => [
                'model' => Plugin\Model::class,
                'currencyFormat' => ZendPlugin\CurrencyFormat::class,
            ],
            'factories' => [
                Plugin\Model::class => InvokableFactory::class,
                ZendPlugin\CurrencyFormat::class => InvokableFactory::class,
            ],
        ];
    	$pluginManager = new HelperPluginManager($container);
        $pluginManager->configure($config);
        return $pluginManager;
    }
}