<?php

use Obullo\Pages\PluginManager;
use Obullo\Pages\Plugin as Plugin;
use Zend\ServiceManager\ServiceManager;
use Interop\Container\ContainerInterface;

class PluginManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new ServiceManager;
        $config = [
            'aliases' => [
                'asset' => Plugin\Asset::class,
            ],
            'factories' => [
                Plugin\Asset::class => function (ContainerInterface $container, $requestedName) {
                    $asset = new Plugin\Asset(ROOT.'/public/', false);
                    return $asset;
                },
            ],
        ];
        $this->pluginManager = new PluginManager($container);
        $this->pluginManager->configure($config);
    }

    public function testAsset()
    {
        $src = $this->pluginManager->asset('/css/test.css');
        $this->assertContains('/css/test.css?v=', $src);
    }
}
