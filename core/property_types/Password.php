<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09.10.2014
 * Time: 1:35
 */

namespace core\field_types;

/**
 * Class Password
 * @package core\field_types
 */
class Password extends String
{
    /**
     * Should be set using setSalt
     * @var string
     */
    private static $salt = '';

    /**
     * Specifies of salt
     * @param string $salt
     */
    public static function setSalt($salt)
    {
        self::$salt = $salt;
    }

    /**
     * Crypts of string with salt
     * @param string $string
     * @return string
     */
    public static function crypt($string)
    {
        if (self::$salt == '') {
            throw new \RuntimeException('Salt not specified');
        }
        return \crypt($string, self::$salt);
    }

    /**
     * Checks password
     * @param string $password
     * @return bool
     */
    public function matched($password)
    {
        return (self::crypt($password) === $this->getValue());
    }
}
