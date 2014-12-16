<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('BASE_PATH', '../');

spl_autoload_register
(
    function($class)
    {
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, BASE_PATH . $class) . '.php';

        if (is_file($path) && is_readable($path)) {
            require_once $path;
            return;
        }
        throw new RuntimeException(sprintf('File "%s" not found or not readable', $path), 500);
    }
);
