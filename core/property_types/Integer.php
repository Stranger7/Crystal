<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 04.10.2014
 * Time: 10:59
 */

namespace core\property_types;

use core\generic\Property;

/**
 * Class Integer
 * @package core\property_types
 */
class Integer extends Property
{
    public function asString($format = self::NOT_INITIALIZED)
    {
        return number_format($this->value);
    }

    public function preparedForDb()
    {
        return strval($this->value);
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
        if (is_null($value) || ($value === ''))
        {
            return self::NOT_INITIALIZED;
        }
        return intval($value);
    }
}
