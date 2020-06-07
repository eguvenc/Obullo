<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class SQLDevelopmentLoggerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setFactory('SQLDevelopmentLogger', 'Obullo\Factory\SQLDevelopmentLoggerFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get('SQLDevelopmentLogger');
        
        $this->assertInstanceOf('Obullo\Logger\LaminasSQLLogger', $instance);
    }
}
