<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 26.09.2014
 * Time: 21:05
 */

namespace core\generic;

use core\DatabaseManager;

/**
 * Class Crystal
 * @package core\generic
 */
abstract class Controller
{
    public function __construct()
    {
    }

    /**
     * @param string $dsn
     * @return DbDriver
     */
    public function db($dsn = '')
    {
        return DatabaseManager::getInstance()->getConn($dsn);
    }
}