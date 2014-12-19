<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 26.10.2014
 * Time: 12:20
 */

namespace core\validators;

use core\generic\Validator;

/**
 * Class isNotZero
 * @package core\validators
 */
class IsNotZero extends Validator
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return ($this->property->get() !== 0);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->property->title() . ' can\'t be equal zero';
    }
}