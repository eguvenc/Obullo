<?php

namespace Obullo\Factory;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Obullo\Logger\LaminasSQLLogger;

/**
 * Sql logger factory for development mode
 */
class SQLDevelopmentLoggerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $logger = new Logger('database');
        $logger->pushHandler(new StreamHandler(ROOT .'/data/log/debug.log', Logger::DEBUG, true, 0666));

        return new LaminasSQLLogger($logger);
    }
}
