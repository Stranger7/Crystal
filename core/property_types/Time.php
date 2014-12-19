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
 * Time: 10:42
 */

namespace core\property_types;

class Time extends DateTime
{
    public function asString($format = 'H:i:s')
    {
        return date($format, $this->value);
    }

    public function preparedForDb()
    {
        return date('H:i:s', $this->value);
    }
}