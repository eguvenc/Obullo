<?php

namespace App\Factory;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Zend\Config\{
    Config,
    Factory,
    Reader\Yaml as YamlReader
};
use Zend\ConfigAggregator\{
    ArrayProvider,
    ConfigAggregator,
    PhpFileProvider
};
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

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
        Factory::registerReader('yaml', new YamlReader([SymfonyYaml::class, 'parse']));
        Factory::setReaderPluginManager($container);

        $preConfig = [
            'config_cache_path' => ROOT.'/var/cache/config.php',
            'routes' => Factory::fromFile(ROOT.'/config/routes.yaml'),
        ];
        $aggregator = new ConfigAggregator(
            [
                new ArrayProvider($preConfig),
                new PhpFileProvider(ROOT.'/config/autoload/*.php'),
            ],
            $preConfig['config_cache_path']
        );
        $config = $aggregator->getMergedConfig();
        $config = new Config($config);

        return $config;
    }
}