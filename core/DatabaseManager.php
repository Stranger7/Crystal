<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 02.10.2014
 * Time: 22:46
 */

namespace core;
use core\generic\DbDriver;
use core\generic\SingletonInterface;

/**
 * Class Database
 * @package core
 */
class DatabaseManager implements SingletonInterface
{
    /**
     * Array of DbDriver objects
     * @var array
     */
    private $connection_pool = [];

    /**
     * @var null|DbDriver
     */
    private $default_conn = null;

    /**
     * It is singleton
     */
    protected static $instance;
    private function __construct() {}
    private function __clone() {}

    /**
     * @return array
     */
    public function getConnectionPool()
    {
        return $this->connection_pool;
    }

    /**
     * @return DatabaseManager
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add DbDriver object to internal array
     * @param string $dsn
     * @param \core\generic\DbDriver $conn
     * @param bool $default
     */
    public function add($dsn, DbDriver $conn, $default = false)
    {
        $this->connection_pool[$dsn] = $conn;
        if ($default) {
            $this->default_conn = $conn;
        }
    }

    /**
     * Returns connection specified by $dsn from pull
     * @param string $dsn
     * @return \core\generic\DbDriver
     * @throws \InvalidArgumentException
     */
    public function getConn($dsn = '')
    {
        if ($dsn === '') {
            if ($this->default_conn === null) {
                if (count($this->connection_pool) == 1) {
                    reset($this->connection_pool);
                    return current($this->connection_pool);
                }
                throw new \InvalidArgumentException("Default connection not defined in config file");
            }
            return $this->default_conn;
        }
        if (!isset($this->connection_pool[$dsn])) {
            throw new \InvalidArgumentException("Invalid database $dsn");
        }
        return $this->connection_pool[$dsn];
    }
}
