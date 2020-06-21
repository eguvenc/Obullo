<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Container\ContainerAwareTrait;
use Obullo\Container\ContainerAwareInterface;

class ContainerAwareTest extends TestCase
{
    use ContainerAwareTrait;

    public function setUp() : void
    {
        $this->container = new ServiceManager;
    }

    public function testContainer()
    {
        $this->setContainer($this->container);
        
        $this->assertInstanceOf('Laminas\ServiceManager\ServiceManager', $this->getContainer());
    }
}
