<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 17.12.2014
 * Time: 2:03
 */

namespace core\validators;

use core\generic\DbDriver;
use core\generic\Validator;

class BelongsTo extends Validator
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
     * @var string
     */
    protected $referenced_to;

    /**
     * @param DbDriver $db
     * @param string $table_name
     * @param string $referenced_to
     */
    public function __construct(DbDriver $db, $table_name, $referenced_to)
    {
        parent::__construct();
        $this->db = $db;
        $this->table_name = $table_name;
        $this->referenced_to = $referenced_to;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ($this->db->getEntry(
            $this->table_name,
            [$this->referenced_to => $this->property->preparedForDb()]
        ));
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->property->get() . ' is not referenced to ' . $this->table_name;
    }
}