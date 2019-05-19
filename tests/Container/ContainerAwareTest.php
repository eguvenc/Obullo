<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Obullo\Container\ContainerAwareTrait;
use Obullo\Container\ContainerAwareInterface;

class ContainerAwareTest extends TestCase
{
    use ContainerAwareTrait;

    public function setUp()
    {
        $this->container = new ServiceManager;
    }

    public function testContainer()
    {
        $this->setContainer($this->container);
        
        $this->assertInstanceOf('Zend\ServiceManager\ServiceManager', $this->getContainer());
    }
}
