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
 * Time: 23:51
 */

namespace core\db_drivers
{
    use core\App;
    use core\db_drivers\query_results\PostgreResult;
    use core\generic\DbDriver;

    /**
     * Class PostgreSQL
     * @package core\db_drivers
     */
    class Postgre extends DbDriver
    {
        /**
         * @var string
         * Example: host=sheep port=5432 dbname=test user=lamb password=bar
         * See documentation on "pg_connect" function, parameter "connection_string"
         */
        private $connection_string = '';

        /**
         * @var int
         * See documentation on "pg_connect" function, parameter "connect_type"
         */
        private $connect_type = 0;

        /*===============================================================*/
        /*                         M E T H O D S                         */
        /*===============================================================*/

        public function __construct()
        {
            parent::__construct();
        }

        /**
         * function for connecting to database
         */
        public function connect()
        {
            // if already connected then return
            if ($this->conn) return;
            // do connect...
            if ($this->getConnectionString() == '') {
                throw new \InvalidArgumentException('PostgreSQL: empty connection string');
            }
            $this->conn = @pg_connect($this->getConnectionString(), $this->getConnectType());
            if (!$this->conn) {
                throw new \RuntimeException("PostgreSQL: Can't connect to database with connection string "
                    . $this->connectionStringWithoutPassword());
            }
            App::logger()->debug("Connected to database " . $this->connectionStringWithoutPassword());
        }

        /**
         * @return string
         */
        public function getConnectionString()
        {
            return $this->connection_string;
        }

        /**
         * @param string $connection_string
         */
        public function setConnectionString($connection_string)
        {
            $this->connection_string = $connection_string;
        }

        /**
         * @return int
         */
        public function getConnectType()
        {
            return $this->connect_type;
        }

        /**
         * @param int $connect_type
         */
        public function setConnectType($connect_type)
        {
            $this->connect_type = intval($connect_type);
        }

        /**
         * @param resource $result
         * @return \core\db_drivers\query_results\PostgreResult
         */
        protected function newQueryResult($result)
        {
            return new PostgreResult($result);
        }

        /**
         * Inserts record to table and returns id of record
         * @param string $table_name
         * @param array $data
         * @param string $id. It is field name
         * @return mixed
         */
        public function createEntry($table_name, $data, $id = '')
        {
            if (empty($id)) {
                throw new \LogicException('Invalid parameters for createEntry function. ' .
                    'ID field name not specified', 501);
            }
            return parent::createEntry($table_name, $data, $id);
        }

        /**
         * @param string $table_name
         * @param array $data
         * @param string $id
         * @return mixed
         */
        protected function doCreateEntry($table_name, $data, $id = '')
        {
            $fields = implode(',', array_keys($data));
            $placeholders = implode(',', array_fill(0, count($data), '?'));
            $sql = 'INSERT' . ' INTO ' . $table_name . ' (' . $fields . ') ' .
                ' VALUES (' . $placeholders . ') RETURNING ' . $id;
            $row = $this->query($sql, array_values($data))->row();
            if ($row) {
                return $row->$id;
            }
            throw new \RuntimeException('Internal error. Can\'t create entry');
        }

        /**
         * @param string $sql
         * @return PostgreResult
         */
        protected function doQuery($sql)
        {
            App::logger()->sql($sql);

            $result = @pg_query($this->getConn(), $sql);
            if (!$result) {
                throw new \RuntimeException("Can't execute query $sql: "
                    . pg_last_error($this->getConn()));
            }

            return $this->newQueryResult($result);
        }

        /**
         * This function has been copied from the framework "CodeIgniter v.3"
         *
         * "Smart" Escape String
         *
         * Escapes data based on type
         *
         * @param	string	$param
         * @return	mixed
         */
        public function escape($param)
        {
            if (is_bool($param)) {
                return ($param) ? 'TRUE' : 'FALSE';
            }
            return parent::escape($param);
        }

        private function connectionStringWithoutPassword()
        {
            return preg_replace(
                '/(password=[.^\S]*)/i',
                'password=****',
                $this->getConnectionString()
            );
        }
    }
}
