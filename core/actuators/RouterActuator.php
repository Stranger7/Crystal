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
     *
     * examples of $description:
     *   / => public\Home::index
     *   /orders => app\web\Order::index
     *   GET:/orders/create => app\web\Orders::create
     *
     *   Called Orders::edit(). Allowed only PUT and POST HTTP-methods
     *       PUT|POST:/orders/edit => app\web\Orders::edit
     *
     *   Called Orders::preview($param1, $param2)
     *       PUT|POST:/orders/preview/%1/%2 => app\web\Orders::preview
     *
     *   Called Orders::table(Variable number of arguments)
     *       /orders/list/+ => app\web\Orders::table
     *
     *   Called Orders::table() without parameters. The residue of the URL is ignored
     *       /orders/list/- => app\web\Orders::table
     */
    public static function parseRoute($description)
    {
        list($uri, $action) = self::splitToUriAndAction($description);
        return array_merge(self::parseUri($uri), self::parseAction($action));
    }

    private static function splitToUriAndAction($description)
    {
        $a = explode('=>', $description);
        if (sizeof($a) != 2) {
            throw new \RuntimeException('Invalid route format: ' . $description, 500);
        }
        return [trim($a[0]), trim($a[1])];
    }

    private static function parseUri($uri)
    {
        $allowed_methods = Router::getRestfulMethods();
        $a = explode(':', $uri, 2);
        if (sizeof($a) == 2)
        {
            $allowed_methods = explode('|', trim(strtoupper($a[0])));
            // Verify $allowed_methods. They must belong to Router::$restful_methods
            array_walk($allowed_methods, function($item) {
                if (!in_array($item, Router::getRestfulMethods())) {
                    throw new \RuntimeException('Invalid request method ' . $item);
                }
            });
            $request_uri = $a[1];
        } else {
            $request_uri = $uri;
        }
        list($request_uri, $param_count) = self::getParamCount(trim(trim($request_uri), '/'));

        return [
            'allowed_methods' => $allowed_methods,
            'request_uri'     => $request_uri,
            'param_count'     => $param_count,
            'pattern'         => self::createPattern($allowed_methods, $request_uri, $param_count)
        ];
    }

    private static function createPattern($allowed_methods, $request_uri, $param_count)
    {
        // Add sub-mask for $allowed_methods
        $pattern = '/^(';
        for($i = 0; $i < count($allowed_methods); $i++) {
            $pattern .= $allowed_methods[$i] .
                (($i == (count($allowed_methods)-1)) ? '' : '|');
        }
        $pattern .= '):';

        // Add module
        $pattern .= str_replace('/', '\/', $request_uri);

        // Add parameters sub-mask
        $pattern .= '(\/\w+)';
        if ($param_count < 0) {
            $pattern .= '{0,}';
        } else {
            $pattern .= '{' . $param_count . '}';
        }

        return ($pattern . '$/i');
    }

    private static function getParamCount($uri)
    {
        $param_count = 0;
        // Check for the presence of modifier "/+" (Variable number of arguments).
        $uri = preg_replace('/(\/\+)$/i', '', $uri, -1, $param_count);
        if (!$param_count) {
            $uri = preg_replace('/(\/\-)$/i', '', $uri, -1, $param_count);
            if (!$param_count) {
                $uri = preg_replace('/(\/%\d+)/i', '', $uri, -1, $param_count);
            } else {
                $param_count = Router::IGNORE_PARAMS;
            }
        } else {
            $param_count = Router::VARIABLE_PARAM_COUNT;
        }
        return [
            $uri,
            $param_count
        ];
    }

    private static function parseAction($action)
    {
        $a = explode('::', $action);
        if (sizeof($a) != 2)
        {
            throw new \RuntimeException('Invalid action format: ' . $action, 500);
        }
        return [
            'class'  => trim($a[0]),
            'method' => trim($a[1])
        ];
    }
}
