<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Escaper\Escaper;

class EscaperFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setFactory(Escaper::class, 'Obullo\Factory\EscaperFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get(Escaper::class);
        
        $this->assertInstanceOf('Laminas\Escaper\Escaper', $instance);
    }
}
