<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 03.10.2014
 * Time: 14:11
 */

namespace core\db_drivers;

use core\App;
use core\db_drivers\query_results\MySQLiResult;
use core\generic\DbDriver;

/**
 * Example of ini-file section
 *
 * [db:url_shorter]
 * driver = MySQLi
 * host = localhost
 * username = url_shorter
 * password = 1234
 * database = url_shorter
 * port = 3306
 * auto_connect = no
 */

/**
 * Class MySQLi
 * @package core\db_drivers
 */
class MySQLi extends DbDriver
{
    private $host = '';
    private $port = '';
    private $username = '';
    private $password = '';
    private $database = '';
    private $socket = '';

    public function __construct()
    {
        parent::__construct();
        $this->setHost(ini_get("mysqli.default_host"));
        $this->setPort(ini_get("mysqli.default_port"));
        $this->setUsername(ini_get("mysqli.default_user"));
        $this->setPassword(ini_get("mysqli.default_pw"));
        $this->setSocket(ini_get("mysqli.default_socket"));
    }

    public function connect()
    {
        // if already connected then return
        if ($this->conn) return;
        // do connect...
        $this->conn = @new \mysqli(
            $this->getHost(),
            $this->getUsername(),
            $this->getPassword(),
            $this->getDatabase(),
            $this->getPort(),
            $this->getSocket()
        );
        if ($this->conn->connect_error) {
            throw new \RuntimeException(
                "Can't connect to database {$this->getDatabase()}."
                . " Error({$this->conn->connect_errno}): "
                . trim($this->conn->connect_error),
                500
            );
        }
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
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
     */
    public function setPort($port)
    {
        $this->port = $port;
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
     */
    public function setUsername($username)
    {
        $this->username = $username;
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
     */
    public function setPassword($password)
    {
        $this->password = $password;
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
     */
    public function setDatabase($database)
    {
        $this->database = $database;
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
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

    /**
     * @param resource $result
     * @return MySQLiResult
     */
    protected function newQueryResult($result)
    {
        return new MySQLiResult($result);
    }

    protected function doCreateEntry($table_name, $data, $id = '')
    {
        $fields = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $sql = 'INSERT' . ' INTO ' . $table_name . ' (' . $fields . ') ' .
            ' VALUES (' . $placeholders . ')';
        if ($this->query($sql, array_values($data))) {
            return $this->conn->insert_id;
        }
        throw new \RuntimeException('Can\'t create entry: ' . $this->conn->error, 500);
    }

    /**
     * Executes query
     * @param string $sql
     * @return mixed
     */
    protected function doQuery($sql)
    {
        App::logger()->sql($sql);

        $result = $this->getConn()->query($sql);
        if (!$result) {
            throw new \RuntimeException("Can't execute query $sql: "
                . "Error({$this->conn->errno}): {$this->conn->error}");
        }
        return (is_bool($result) ? true : $this->newQueryResult($result));
    }
}
