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
 * Time: 23:36
 */

namespace core\generic;

use core\Utils;

abstract class DbDriver
{
    /**
     * @var resource|\mysqli
     */
    protected $conn = null;

    /**
     * @var string
     */
    protected $bind_marker = '?';

    /**
     * ESCAPE character
     *
     * @var	string
     */
    protected $like_escape_chr = '!';

    /*===============================================================*/
    /*                         M E T H O D S                         */
    /*===============================================================*/

    public function __construct() {}

    /**
     * Create connection with database
     */
    abstract public function connect();

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
    abstract protected function newQueryResult($result);

    /**
     * Inserts record to table and returns id of record
     * @param string $table_name
     * @param array $data
     * @param string $id. Primary key field name
     * @return mixed. Primary key value
     */
    public function createEntry($table_name, $data, $id = '')
    {
        $this->checkTableName($table_name, 'createEntry');
        return $this->doCreateEntry($table_name, $data, $id);
    }

    abstract protected function doCreateEntry($table_name, $data, $id = '');

    /**
     * Updates record
     * @param string $table_name
     * @param array $data
     * @param array $id = [field_name => value]
     * @return bool
     */
    public function updateEntry($table_name, $data, $id)
    {
        $this->checkTableName($table_name, 'updateEntry');
        $this->idValidation($id, 'updateEntry');
        return $this->doUpdateEntry($table_name, $data, $id);
    }

    /**
     * @param string $table_name
     * @param array $data
     * @param array $id
     * @return bool
     */
    protected function doUpdateEntry($table_name, $data, $id)
    {
        $fields = implode(
            ',',
            array_map(
                function($field) {
                    return ($field . ' = ' . $this->bind_marker);
                },
                array_keys($data)
            )
        );
        $where = key($id) . ' = ' . $this->escape(current($id));
        $sql = 'UPDATE ' . $table_name . ' SET ' . $fields . ' WHERE ' . $where;
        return $this->query($sql, array_values($data)) ? true : false;
    }

    /**
     * Deletes record
     * @param $table_name
     * @param array $id = [field_name => value]
     * @return bool
     */
    public function deleteEntry($table_name, $id)
    {
        $this->checkTableName($table_name, 'deleteEntry');
        $this->idValidation($id, 'deleteEntry');
        return $this->doDeleteEntry($table_name, $id);
    }

    /**
     * @param $table_name
     * @param array $id
     * @return bool
     */
    protected function doDeleteEntry($table_name, $id)
    {
        $where = key($id) . ' = ' . $this->escape(current($id));
        $sql = 'DELETE' . ' FROM ' . $table_name . ' WHERE ' . $where;
        return $this->doQuery($sql) ? true : false;
    }

    /**
     * Do select one record
     * @param string $table_name
     * @param array $id
     * @param string $select
     * @return bool|object
     */
    public function getEntry($table_name, $id, $select = '*')
    {
        $this->checkTableName($table_name, 'getEntry');
        $this->idValidation($id, 'getEntry');
        $where = key($id) . ' = ' . $this->escape(current($id));
        $sql = 'SELECT ' . $select . ' FROM ' . $table_name . ' WHERE ' . $where;
        return $this->doQuery($sql)->row();
    }

    /**
     * Returns list of entries from specified table
     *
     * @param $table_name
     * @param string $order_by
     * @param int $limit
     * @param int $offset
     * @return mixed|\core\db_drivers\query_results\QueryResult
     */
    public function entries($table_name, $order_by = '', $limit = 0, $offset = 0)
    {
        $this->checkTableName($table_name, 'entries');
        $sql = 'SELECT ' . '*' . ' FROM ' . $table_name;
        if (!empty($order_by)) {
            $sql .= ' ORDER BY ' . $order_by;
        }
        if (!empty($offset)) {
            $sql .= ' LIMIT '. $limit . ' OFFSET ' . $offset;
        }
        return $this->doQuery($sql);
    }

    /**
     * Deletes all rows from specified table
     * @param $table_name
     * @return bool
     */
    public function clearTable($table_name)
    {
        $this->checkTableName($table_name, 'clearTable');
        $sql = 'DELETE' . ' FROM ' . $table_name;
        return $this->doQuery($sql) ? true : false;
    }

    /**
     * Prepare query and call function "doQuery" for execute
     * @param string $sql
     * @param array $params
     * @return bool|\core\db_drivers\query_results\QueryResult
     */
    public function query($sql, $params = [])
    {
        return $this->doQuery($this->compileBind($sql, $params));
    }

    /**
     * Executes query
     * @param string $sql
     * @return mixed
     */
    abstract protected function doQuery($sql);

    /**
     * This function has been copied from the framework "CodeIgniter v.3"
     *
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @param	string $param
     * @return	mixed
     */
    public function escape($param)
    {
        if (is_array($param))
        {
            $param = array_map([&$this, 'escape'], $param);
            return $param;
        }
        elseif (is_string($param) OR (is_object($param) && method_exists($param, '__toString')))
        {
            return "'" . $this->escapeString($param) . "'";
        }
        elseif (is_bool($param))
        {
            return ($param === FALSE) ? 0 : 1;
        }
        elseif ($param === NULL)
        {
            return 'NULL';
        }
        return $param;
    }

    /**
     * This function has been copied from the framework "CodeIgniter v.3"
     *
     * Escape String
     *
     * @param	string	$value
     * @param	bool	$like	Whether or not the string will be used in a LIKE condition
     * @return	string
     */
    public function escapeString($value, $like = false)
    {
        if (is_array($value))
        {
            foreach ($value as $key => $val)
            {
                $value[$key] = $this->escapeString($val, $like);
            }
            return $value;
        }

        $value = $this->escapeApostrophe($value);

        // escape LIKE condition wildcards
        if ($like === true)
        {
            return str_replace(
                [
                    $this->like_escape_chr, '%', '_'
                ], [
                    $this->like_escape_chr . $this->like_escape_chr,
                    $this->like_escape_chr . '%',
                    $this->like_escape_chr . '_'
                ],
                $value
            );
        }

        return $value;
    }

    /**
     * This function has been copied from the framework "CodeIgniter v.3"
     *
     * Platform-dependant string escape
     *
     * @param	string $str
     * @return	string
     */
    protected function escapeApostrophe($str)
    {
        return str_replace("'", "''", Utils::removeInvisibleCharacters($str));
    }

    /**
     * Replaces placeholder with values
     * This function has been copied from the framework "CodeIgniter v.3"
     * @param string $sql
     * @param array $binds
     * @return string
     */
    protected function compileBind($sql, $binds = [])
    {
        if (empty($binds) OR empty($this->bind_marker) OR strpos($sql, $this->bind_marker) === false) {
            return $sql;
        } elseif (!is_array($binds)) {
            $binds = [$binds];
            $bind_count = 1;
        } else {
            // Make sure we're using numeric keys
            $binds = array_values($binds);
            $bind_count = count($binds);
        }

        // We'll need the marker length later
        $ml = strlen($this->bind_marker);

        // Make sure not to replace a chunk inside a string that happens to match the bind marker
        if ($c = preg_match_all("/'[^']*'/i", $sql, $matches)) {
            $c = preg_match_all('/'.preg_quote($this->bind_marker, '/').'/i',
                str_replace($matches[0],
                    str_replace($this->bind_marker, str_repeat(' ', $ml), $matches[0]),
                    $sql, $c),
                $matches, PREG_OFFSET_CAPTURE);

            // Bind values' count must match the count of markers in the query
            if ($bind_count !== $c) {
                return $sql;
            }
        } elseif (($c = preg_match_all(
                '/' . preg_quote($this->bind_marker, '/') . '/i',
                $sql,
                $matches, PREG_OFFSET_CAPTURE)) !== $bind_count)
        {
            return $sql;
        }

        do {
            $c--;
            $escaped_value = $this->escape($binds[$c]);
            if (is_array($escaped_value))
            {
                $escaped_value = '('.implode(',', $escaped_value).')';
            }
            $sql = substr_replace($sql, $escaped_value, $matches[0][$c][1], $ml);
        } while ($c !== 0);

        return $sql;
    }

    protected function checkTableName($table_name, $function_name)
    {
        if (empty($table_name)) {
            throw new \LogicException('Invalid parameters for "' . $function_name . '" function. ' .
                'Table name not specified', 501);
        }
    }

    protected function idValidation($id, $function_name)
    {
        if (!is_array($id) || count($id) != 1) {
            throw new \LogicException('Invalid parameters for "' . $function_name . '" function. ' .
                'ID must be array: [table_field_name => value]', 501);
        }
    }

    public function __toString()
    {
        return __CLASS__;
    }
}
