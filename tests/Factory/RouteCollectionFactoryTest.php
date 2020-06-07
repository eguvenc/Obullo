<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

use Obullo\Router\RouteCollectionInteface;
use Obullo\Router\Pattern;

class RouteCollectionFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setFactory(RouteCollectionInterface::class, 'Obullo\Factory\RouteCollectionFactory');
    }

    public function testFactory()
    {
        $options['config']['router']['translatable_routes'] = false;
        $options['pattern'] = new Pattern;

        $instance = $this->container->build(RouteCollectionInterface::class, $options);

        $this->assertInstanceOf('Obullo\Router\RouteCollection', $instance);
    }
}
