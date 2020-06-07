<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Obullo\Logger\DoctrineSQLLogger;

class DoctrineSQLLoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (file_exists(ROOT .'/data/log/debug.log')) {
            unlink(ROOT .'/data/log/debug.log');
        }
        $logger = new Logger('tests');
        $logger->pushHandler(new StreamHandler(ROOT .'/data/log/debug.log', Logger::DEBUG, true, 0666));
        
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

        $debugLog = file_get_contents(ROOT .'/data/log/debug.log');

        $sql1 = '] tests.DEBUG: SQL-1: SELECT * FROM users WHERE id = ? AND name = ? {"params":[5,"test"],';
        $sql2 = '] tests.DEBUG: SQL-2: SELECT * FROM users WHERE id = :id AND name = :name {"params":{"name":"test","id":6},';

        $this->assertContains($sql1, $debugLog);
        $this->assertContains($sql2, $debugLog);
    }
}
