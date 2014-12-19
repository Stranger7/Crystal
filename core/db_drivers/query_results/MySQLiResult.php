<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 16.12.2014
 * Time: 16:26
 */

namespace core\db_drivers\query_results;

/**
 * Class MySQLResult
 * @package core\db_drivers\query_results
 */
class MySQLiResult extends QueryResult
{
    /**
     * @return null|object
     */
    public function row()
    {
        return ($this->result->num_rows > 0) ? $this->result->fetch_object() : false;
    }

    /**
     * @return array|null
     */
    public function result()
    {
        return ($this->result->num_rows > 0) ? $this->result->fetch_all(MYSQLI_ASSOC) : [];
    }
}