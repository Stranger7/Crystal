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
 * Time: 10:32
 */

namespace core\property_types;

/**
 * Class Date
 * @package core\property_types
 */
class Date extends DateTime
{
    /**
     * @return string
     */
    public function type()
    {
        return 'DATE';
    }

    /**
     * @param string $format
     * @return bool|string
     */
    public function asString($format = 'd.m.Y')
    {
        return date($format, $this->value);
    }

    /**
     * @return bool|string
     */
    public function preparedForDb()
    {
        return date('Y-m-d', $this->value);
    }
}