<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Obullo\Logger\LaminasSQLLogger;

use Laminas\Db\Adapter\StatementContainer;
use Laminas\Db\Adapter\ParameterContainer;

class LaminasSQLLoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__ . '/../config/application.config.php';
        $this->root = $appConfig['root'];
        
        $this->file = $this->root.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'debug.log';
        $logger = new Logger('tests');
        $logger->pushHandler(new StreamHandler($this->file, Logger::DEBUG, true, 0666));
        
        $this->logger = $logger;
        $this->sqlLogger = new LaminasSQLLogger($this->logger);
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

        $debugLog = file_get_contents($this->file);

        $sql1 = '] tests.DEBUG: SQL-1: SELECT * FROM users WHERE id = ? AND name = ? {"params":[5,"test"],';
        $sql2 = '] tests.DEBUG: SQL-2: SELECT * FROM users WHERE id = :id AND name = :name {"params":{"id":6,"name":"test"},';

        $this->assertContains($sql1, $debugLog);
        $this->assertContains($sql2, $debugLog);

        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }
}
