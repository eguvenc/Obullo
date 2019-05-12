<?php

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Zend\ServiceManager\ServiceManager;
use Obullo\Logger\LoggerAwareTrait;

class LoggerAwareTest extends TestCase
{
    use LoggerAwareTrait;

    public function setUp()
    {
        $this->logger = new Logger('tests');
    }

    public function testLogger()
    {
        $this->setLogger($this->logger);
        
        $this->assertInstanceOf('Monolog\Logger', $this->getLogger());
    }
}
