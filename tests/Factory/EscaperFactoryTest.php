<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Escaper\Escaper;

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
        
        $this->assertInstanceOf('Zend\Escaper\Escaper', $instance);
    }
}
