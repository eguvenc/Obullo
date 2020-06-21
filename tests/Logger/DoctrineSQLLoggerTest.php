<?php

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Obullo\Logger\DoctrineSQLLogger;

class DoctrineSQLLoggerTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__ . '/../config/application.config.php';
        $this->root = $appConfig['root'];

        if (file_exists($this->root .'/data/log/debug.log')) {
            unlink($this->root .'/data/log/debug.log');
        }
        $logger = new Logger('tests');
        $logger->pushHandler(new StreamHandler($this->root .'/data/log/debug.log', Logger::DEBUG, true, 0666));
        
        $this->logger = $logger;
        $this->sqlLogger = new DoctrineSQLLogger($this->logger);
    }

    public function testStartQuery()
    {
        // SQL-1
        $this->sqlLogger->startQuery("SELECT * FROM users WHERE id = ? AND name = ?", array(5,'test'));
        $this->sqlLogger->stopQuery();

        // SQL-2
        $this->sqlLogger->startQuery("SELECT * FROM users WHERE id = :id AND name = :name", array('name' => 'test', 'id' => 6));
        $this->sqlLogger->stopQuery();

        $debugLog = file_get_contents($this->root .'/data/log/debug.log');

        $sql1 = '] tests.DEBUG: SQL-1: SELECT * FROM users WHERE id = ? AND name = ? {"params":[5,"test"],';
        $sql2 = '] tests.DEBUG: SQL-2: SELECT * FROM users WHERE id = :id AND name = :name {"params":{"name":"test","id":6},';

        $this->assertStringContainsString($sql1, $debugLog);
        $this->assertStringContainsString($sql2, $debugLog);
    }
}
