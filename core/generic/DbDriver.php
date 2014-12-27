<?php
/**
 * This file is part of the Crystal framework.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 02.10.2014
 * Time: 23:36
 */

namespace core\generic;

abstract class DbDriver
{
    /**
     * @var resource|\mysqli
     */
    protected $conn = null;

    private $host = '';
    private $port = '';
    private $socket = '';
    private $username = '';
    private $password = '';
    private $database = '';

    /*===============================================================*/
    /*                         M E T H O D S                         */
    /*===============================================================*/

    public function __construct() {}

    /**
     * Create connection with database
     */
    abstract public function connect();

    /**
     * Close connection
     * @return bool
     */
    abstract public function disconnect();

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return DbDriver
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     * @return DbDriver
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return DbDriver
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return DbDriver
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $database
     * @return DbDriver
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @return string
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param string $socket
     * @return DbDriver
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return resource|\MySQLi
     */
    protected function getConn()
    {
        if (empty($this->conn)) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * @param mixed $result
     * @return \core\db_drivers\query_results\QueryResult
     */
    abstract protected function queryResult($result);

    /**
     * @return \core\db_drivers\sql_builders\SqlBuilder
     */
    abstract protected function sqlBuilder();

    /**
     * @param array $fields
     * @return \core\db_drivers\sql_builders\SqlBuilder
     */
    public function select($fields = [])
    {
        return $this->sqlBuilder()->setDb($this)->select($fields);
    }

    /**
     * Inserts record to table and returns id of record
     * @param string $table_name
     * @param array $data
     * @return mixed. Primary key value
     */
    abstract public function insert($table_name, $data);

    /**
     * Updates record
     * @param string $table_name
     * @param array $data
     * @return \core\db_drivers\sql_builders\SqlBuilder
     */
    public function update($table_name, $data)
    {
        return $this->sqlBuilder()->setDb($this)->update($table_name, $data);
    }

    /**
     * Deletes record
     * @param $table_name
     * @return \core\db_drivers\sql_builders\SqlBuilder
     */
    public function delete($table_name)
    {
        return $this->sqlBuilder()->setDb($this)->delete($table_name);
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return \core\db_drivers\query_results\QueryResult|null
     */
    public function query($sql, $binds = [])
    {
        return empty($binds)
            ? $this->doQuery($sql)
            : $this->sqlBuilder()->setDb($this)->custom($sql, $binds)->run();
    }

    /**
     * Performs query
     * @param string $sql
     * @return null|\core\db_drivers\query_results\QueryResult
     */
    abstract protected function doQuery($sql);

    /**
     * @return string
     */
    public function __toString()
    {
        return __CLASS__;
    }
}
