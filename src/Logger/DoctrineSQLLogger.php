<?php

namespace Obullo\Logger;

use Psr\Log\LoggerInterface as Logger;
use Doctrine\DBAL\Logging\SQLLogger as SQLLoggerInterface;

/**
 * SQLLogger for Doctrine DBAL
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class DoctrineSQLLogger implements SQLLoggerInterface
{
    /**
     * Sql
     *
     * @var string
     */
    protected $sql;

    /**
     * Query timer start value
     *
     * @var int
     */
    protected $start;

    /**
     * Logger
     *
     * @var object
     */
    protected $logger;

    /**
     * Bind parameters
     *
     * @var array
     */
    protected $params;

    /**
     * Count number of queries
     *
     * @var integer
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
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);
        $this->params = $params;
        ++$this->currentIndex;
        $this->sql = $sql;
        $types = null;
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $end = microtime(true);

        $this->logger->debug(
            'SQL-'.$this->currentIndex.': '.$this->sql,
            [
                'params' => $this->params,
                'time'=> number_format($end - $this->start, 4),
            ]
        );
    }

}