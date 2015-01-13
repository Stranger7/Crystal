<?php
/**
 * This file is part of the Crystal framework.
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
use core\interfaces\Actuator;
use core\Router;

/**
 * Static Class RouterInitializer
 * @package core\loaders
 */
class RouterActuator implements Actuator
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
     */
    public static function parseRoute($description)
    {
        list($uri, $action) = self::splitToUriAndAction($description);
        return array_merge(self::parseUri($uri), self::parseAction($action));
    }

    /**
     * @param string $description
     * @return array
     */
    private static function splitToUriAndAction($description)
    {
        $a = explode('=>', $description);
        if (sizeof($a) != 2) {
            throw new \RuntimeException('Invalid route format: ' . $description, 500);
        }
        return [trim($a[0]), trim($a[1])];
    }

    /**
     * @param string $uri
     * @return array
     */
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
        list($request_uri, $param_count) = self::extractUriAndParamCount(trim(trim($request_uri), '/'));

        return [
            'allowed_methods' => $allowed_methods,
            'request_uri'     => $request_uri,
            'param_count'     => $param_count,
            'pattern'         => self::createPattern($allowed_methods, $request_uri, $param_count)
        ];
    }

    /**
     * @param array $allowed_methods
     * @param string $request_uri
     * @param int $param_count
     * @return string
     */
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
        if ($param_count <= 0) {
            $pattern .= '{0}';
        } else {
            $pattern .= '{' . $param_count . '}';
        }

        return ($pattern . '$/i');
    }

    /**
     * @param string $uri
     * @return array
     */
    private static function extractUriAndParamCount($uri)
    {
        $a = preg_split('/(\/%)/', $uri);
        $param_count = count($a) - 1;
        $uri = $a[0];
        return [
            $uri,
            $param_count
        ];
    }

    /**
     * @param string $action
     * @return array
     */
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
