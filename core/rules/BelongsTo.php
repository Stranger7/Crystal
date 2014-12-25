<?php
/**
 * This file is part of the Crystal framework.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 17.12.2014
 * Time: 2:03
 */

namespace core\rules;

use core\generic\DbDriver;
use core\generic\Rule;

/**
 * Class BelongsTo
 * @package core\rules
 */
class BelongsTo extends Rule
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
        return $this->db
            ->select()
            ->from($this->table_name)
            ->where("{$this->referenced_to} = ?", $this->property->preparedForDb())
            ->run()->row();
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->property->get() . ' does not refer to ' . $this->table_name;
    }
}