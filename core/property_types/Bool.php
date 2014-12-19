<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 10.12.2014
 * Time: 20:38
 */

namespace core\property_types;

use core\generic\Property;
use core\Utils;

/**
 * Class Bool
 * @package core\property_types
 */
class Bool extends Property
{
    private $format = ['TRUE', 'FALSE'];

    /**
     * @param array $format
     * @return string
     */
    public function asString($format = [])
    {
        if (is_array($format) && count($format) == 2) {
            $this->format = $format;
        }
        return ($this->value ? ((string) $this->format[0]) : ((string) $this->format[1]));
    }

    public function preparedForDb()
    {
        return ($this->value ? $this->format[0] : $this->format[1]);
    }

    public function isEmpty()
    {
        return (!$this->initialized());
    }

    /**
     * Converts to bool
     * @param mixed $value
     * @return bool
     */
    protected function cast($value)
    {
        if (is_string($value)) {
            return Utils::boolValue($value);
        }
        return boolval($value);
    }
}