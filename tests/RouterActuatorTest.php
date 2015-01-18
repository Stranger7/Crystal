<?php
/**
 * This file is part of the Crystal framework.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 16.01.2015
 * Time: 17:51
 */

namespace tests;

use core\actuators\RouterActuator;

/**
 * Class routerTest
 * @package tests
 */
class RouterActuatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $description
     * @param array $allowed_methods
     * @param string $cleared_uri
     * @param int $required_param_count
     * @param int $optional_param_count
     * @param string $pattern
     * @param string $class
     * @param string $method
     */
    public function testParseRoute($description,
                                   $allowed_methods,
                                   $cleared_uri,
                                   $required_param_count,
                                   $optional_param_count,
                                   $pattern,
                                   $class,
                                   $method)
    {
        $route_descriptor = RouterActuator::parseRoute($description);
        $this->assertEquals($route_descriptor['allowed_methods'], $allowed_methods);
        $this->assertEquals($route_descriptor['cleared_uri'], $cleared_uri);
        $this->assertEquals($route_descriptor['required_param_count'], $required_param_count);
        $this->assertEquals($route_descriptor['optional_param_count'], $optional_param_count);
        $this->assertEquals($route_descriptor['pattern'], $pattern);
        $this->assertEquals($route_descriptor['class'], $class);
        $this->assertEquals($route_descriptor['method'], $method);
    }

    public function provider()
    {
        return [
            [
                'description'          => 'GET:/order/create => app\web\Order::create',
                'allowed_methods'      => ['GET'],
                'cleared_uri'          => 'order/create',
                'required_param_count' => 0,
                'optional_param_count' => 0,
                'pattern'              => '/^(GET):\/order\/create(\/\w+){0,0}$/i',
                'class'                => 'app\web\Order',
                'method'               => 'create',
            ],
            [
                'description'          => 'GET:/order/view?/%1 => app\web\Order::view',
                'allowed_methods'      => ['GET'],
                'cleared_uri'          => 'order/view',
                'required_param_count' => 1,
                'optional_param_count' => 0,
                'pattern'              => '/^(GET):\/order\/view(\/\w+){1,1}$/i',
                'class'                => 'app\web\Order',
                'method'               => 'view',
            ],
            [
                'description'          => 'POST:/order/save => app\web\Order::save',
                'allowed_methods'      => ['POST'],
                'cleared_uri'          => 'order/save',
                'required_param_count' => 0,
                'optional_param_count' => 0,
                'pattern'              => '/^(POST):\/order\/save(\/\w+){0,0}$/i',
                'class'                => 'app\web\Order',
                'method'               => 'save',
            ],
        ];
    }
}