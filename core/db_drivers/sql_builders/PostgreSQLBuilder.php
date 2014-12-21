<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 21.12.2014
 * Time: 18:10
 */

namespace core\db_drivers\sql_builders;


class PostgreSQLBuilder extends SqlBuilder
{
    /**
     * @var mixed
     *
     * Name of id field
     */
    protected $id_field_name;

    /**
     * @param string $table_name
     * @param array $data
     * @return $this
     */
    public function insert($table_name, $data, $id)
    {
        parent::insert($table_name, $data);
        $this->id_field_name = $id;
        return $this;
    }

    /**
     * @return string
     */
    protected function insertPattern()
    {
        return parent::insertPattern() . ' RETURNING ' . $this->id_field_name;
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
}