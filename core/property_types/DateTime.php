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
 * Time: 2:19
 */

namespace core\property_types;

use core\generic\Property;

/**
 * Class DateTime
 * @package core\property_types
 */
class DateTime extends Property
{
    public function asString($format = 'd.m.Y H:i:s')
    {
        return date($format, $this->value);
    }

    public function preparedForDb()
    {
        return date('Y-m-d H:i:s', $this->value);
    }

    public function isEmpty()
    {
        return empty($this->value);
    }

    /**
     * Converts to int
     * @param mixed $value
     * @return int|null
     */
    protected function cast($value)
    {
        if (empty($value))
        {
            return self::NOT_INITIALIZED;
        }
        return is_numeric($value) ? intval($value) : strtotime($value);
    }
}