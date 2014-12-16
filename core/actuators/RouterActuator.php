<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 30.09.2014
 * Time: 11:14
 */

namespace core\actuators;

use core\App;
use core\Config;
use core\Router;

/**
 * Static Class RouterInitializer
 * @package core\loaders
 */
class RouterActuator implements ActuatorInterface
{
    /**
     * Parse config-file and creating of route array
     * @return \core\Router
     */
    public static function run()
    {
        $router = new Router();
        foreach(App::config()->get(Config::ROUTES_SECTION) as $route => $description)
        {
            $router->addRoute($route, self::parseRoute($description));
        }

        App::logger()->debug("Routes for [" . App::name(). "] parsed");

        return $router;
    }

    /**
     * Parse string of route from config file
     * @param string $description
     * @return array
     * examples of $description_str:
     *   / > public\Home:index
     *   /orders > user\Order:index
     *   GET::/orders/create > user\Orders:create
     *   PUT,POST::/orders/edit/{id,prefix} > user\Orders:edit
     */
    public static function parseRoute($description)
    {
        $descriptor = [];
        list($descriptor['request_methods'], $rest) = self::parseRestMethodsPart($description);
        $a = explode('>', $rest);
        if (count($a) != 2) {
            throw new \RuntimeException('Invalid structure of route description ' . $description .
                '. Pointer symbol ">" to class:action not found ');
        }
        list($descriptor['request_uri'], $descriptor['parameters']) = self::parseRequestUriPart($a[0]);
        list($descriptor['class'], $descriptor['action']) = self::parseActionPart($a[1]);

        return $descriptor;
    }

    /**
     * Specifies allowed REST-method for action
     * @param string $description
     * @return array
     */
    private static function parseRestMethodsPart($description)
    {
        $a = explode('::', $description, 2);
        if (count($a) == 2)
        {
            $request_methods = explode(',', trim(strtoupper($a[0])));
            foreach($request_methods as $request_method)
            {
                if (!in_array($request_method, Router::getAllowedRestMethods()))
                {
                    throw new \RuntimeException('Invalid request method ' . $request_method);
                }
            }
            $rest = $a[1];
        } else {
            $request_methods = Router::getAllowedRestMethods();
            $rest = $a[0];
        }
        return [$request_methods, $rest];
    }

    /**
     * Split description of URI to action and parameters
     * @param string $request_uri
     * @return array
     */
    private static function parseRequestUriPart($request_uri)
    {
        $a = explode('{', trim($request_uri));
        return [
            $a[0],
            (isset($a[1]) ? self::createParamArray(rtrim($a[1], '}')) : [])
        ];
    }

    /**
     * Splits action to class and method
     * @param string $action
     * @return array
     */
    private static function parseActionPart($action)
    {
        $a = explode(':', $action);
        if (count($a) != 2)
        {
            throw new \RuntimeException('Invalid structure of route description ' .
                '. Delimiter symbol ":" between class and action not found in string ' . $action);
        }
        return [trim($a[0]), trim($a[1])];
    }

    /**
     * Creates array for parameters
     * @param string $params
     * @return array
     */
    private static function createParamArray($params)
    {
        return array_fill_keys(explode(',', $params), '');
    }
}
