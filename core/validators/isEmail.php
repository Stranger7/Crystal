<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 07.10.2014
 * Time: 0:07
 */


namespace core\validators;

use core\generic\Validator;

/**
 * Class isEmail
 * @package core\validators
 */
class isEmail extends Validator
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return filter_var($this->property->get(), FILTER_VALIDATE_EMAIL);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->property->get() . ' is not valid E-mail';
    }
} 
