<?php

namespace Obullo\Factory;

use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Obullo\Logger\LaminasSQLLogger;

class LaminasSQLLoggerFactory implements FactoryInterface
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
        return new LaminasSQLLogger($container->get(LoggerInterface::class));
    }
}
