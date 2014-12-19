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
 * Time: 22:43
 */

namespace core\validators;


use core\generic\DbDriver;
use core\generic\Property;
use core\generic\Validator;

class IsUnique extends Validator
{
    /**
     * @var DbDriver
     */
    protected $db;

    /**
     * @var string
     */
    protected $table_name;

    /**
     * @var
     */
    protected $id;

    /**
     * @param DbDriver $db
     * @param string $table_name
     * @param Property $id
     */
    public function __construct(DbDriver $db, $table_name, Property $id)
    {
        parent::__construct();
        $this->db = $db;
        $this->table_name = $table_name;
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $params = [];
        $sql = 'SELECT ' . '*' .' FROM ' . $this->table_name
            . ' WHERE ' . $this->property->name() . ' = ?';
        $params[] = $this->property->get();
        if (!$this->id->isEmpty()) {
            $sql .= ' AND ' . $this->id->name() . ' <> ?';
            $params[] = $this->id->get();
        }
        return !$this->db->query($sql, $params)->row();
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return "value '{$this->property->get()}' is not unique for field `{$this->property->name()}`";
    }
}