<?php

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Logger\LoggerAwareTrait;

class LoggerAwareTest extends TestCase
{
    use LoggerAwareTrait;

    public function setUp() : void
    {
        $this->logger = new Logger('tests');
    }

    public function testLogger()
    {
        $this->setLogger($this->logger);
        
        $this->assertInstanceOf('Monolog\Logger', $this->getLogger());
    }
}
