<?php

namespace Obullo\Logger;

use Zend\Db\Adapter\Profiler\ProfilerInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Adapter\Exception;
use Psr\Log\LoggerInterface as Logger;

/**
 * SQLLogger for Zend DB
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class ZendSQLLogger implements ProfilerInterface
{    
    /**
     * Logger
     *
     * @var object
     */
    protected $logger;

  	/**
     * @var array
     */
    protected $profiles = [];

    /**
     * @var null
     */
    protected $currentIndex = 0;

    /**
     * Create pdo statement object
     *
     * @param \Psr\Log\Logger $logger object
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string|StatementContainerInterface $target
     * @return self Provides a fluent interface
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     */
    public function profilerStart($target)
    {
        $profileInformation = [
            'sql' => '',
            'parameters' => null,
            'start' => microtime(true),
            'end' => null,
            'elapse' => null
        ];
        if ($target instanceof StatementContainerInterface) {
            $profileInformation['sql'] = $target->getSql();
            $profileInformation['parameters'] = clone $target->getParameterContainer();
        } elseif (is_string($target)) {
            $profileInformation['sql'] = $target;
        } else {
            throw new Exception\InvalidArgumentException(
                __FUNCTION__ . ' takes either a StatementContainer or a string'
            );
        }

        $this->profiles[$this->currentIndex] = $profileInformation;

        return $this;
    }

    /**
     * @return self Provides a fluent interface
     */
    public function profilerFinish()
    {
        if (! isset($this->profiles[$this->currentIndex])) {
            throw new Exception\RuntimeException(
                'A profile must be started before ' . __FUNCTION__ . ' can be called.'
            );
        }
        $current = &$this->profiles[$this->currentIndex];
        $current['end'] = microtime(true);
        $current['elapse'] = $current['end'] - $current['start'];

        $this->currentIndex++;
        $parameters = $current['parameters']->getNamedArray();

        $this->logger->debug(
            'SQL-'.$this->currentIndex.': '.$current['sql'],
            [
                'params' => $parameters,
                'time'=> number_format($current['elapse'], 4),
            ]
        );

        return $this;
    }
    
    /**
     * @return array|null
     */
    public function getLastProfile()
    {
        return end($this->profiles);
    }

    /**
     * @return array
     */
    public function getProfiles()
    {
        return $this->profiles;
    }
}