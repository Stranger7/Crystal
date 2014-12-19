<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 03.10.2014
 * Time: 23:24
 */

namespace core\validators;

use core\generic\Validator;

/**
 * Class Required
 * @package core\validators
 */
class IsRequired extends Validator
{
    /**
     * @return bool
     */
    public function isValid()
    {
        // for compatibility with PHP version less then 5.5
        $value = $this->property->get();
        return (!empty($value));
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->property->title() . ' is required';
    }
}
