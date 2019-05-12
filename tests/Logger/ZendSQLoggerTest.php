<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Obullo\Logger\ZendSQLLogger;

use Zend\Db\Adapter\StatementContainer;
use Zend\Db\Adapter\ParameterContainer;

class ZendSQLLoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (file_exists(ROOT .'/var/log/debug.log')) {
            unlink(ROOT .'/var/log/debug.log');
        }
        $logger = new Logger('tests');
        $logger->pushHandler(new StreamHandler(ROOT .'/var/log/debug.log', Logger::DEBUG, true, 0666));
        
        $this->logger = $logger;
        $this->sqlLogger = new ZendSQLLogger($this->logger);
    }

    public function testProfiler()
    {
        $container = new StatementContainer;
        $params = new ParameterContainer;
        $params[] = 5;
        $params[] = 'test';
        $container->setSql("SELECT * FROM users WHERE id = ? AND name = ?");
        $container->setParameterContainer($params);

        // SQL-1
        $this->sqlLogger->profilerStart($container, array(5,'test'));
        $this->sqlLogger->profilerFinish();

        // $profile1 = $this->sqlLogger->getLastProfile();

        $container = new StatementContainer;
        $params = new ParameterContainer;
        $params['id'] = 6;
        $params['name'] = 'test';
        $container->setSql("SELECT * FROM users WHERE id = :id AND name = :name");
        $container->setParameterContainer($params);

        // SQL-2
        $this->sqlLogger->profilerStart($container, array(6,'test'));
        $this->sqlLogger->profilerFinish();

        $debugLog = file_get_contents(ROOT .'/var/log/debug.log');

        $sql1 = '] tests.DEBUG: SQL-1: SELECT * FROM users WHERE id = ? AND name = ? {"params":[5,"test"],';
        $sql2 = '] tests.DEBUG: SQL-2: SELECT * FROM users WHERE id = :id AND name = :name {"params":{"id":6,"name":"test"},';

        $this->assertContains($sql1, $debugLog);
        $this->assertContains($sql2, $debugLog);
    }
}
