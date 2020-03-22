<?php

namespace App\Factory;

use Obullo\View\Helper as Plugin;
use Laminas\I18n\View\Helper as LaminasPlugin;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\I18n\View\Helper;
use Laminas\View\HelperPluginManager;

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
                'currencyFormat' => LaminasPlugin\CurrencyFormat::class,
            ],
            'factories' => [
                Plugin\Model::class => InvokableFactory::class,
                LaminasPlugin\CurrencyFormat::class => InvokableFactory::class,
            ],
        ];
    	$pluginManager = new HelperPluginManager($container);
        $pluginManager->configure($config);
        return $pluginManager;
    }
}