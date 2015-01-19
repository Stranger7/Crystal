<?php
/**
 * This file is part of the Crystal framework.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 19.01.2015
 * Time: 19:05
 */

namespace tests;

use core\Router;
use core\Utils;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $name
     * @param array $descriptor
     * @param array $params
     * @param string $url
     */
    public function testMakeUrl($name, $descriptor, $params, $url)
    {
        $router = new Router();
        $router->addRoute($name, $descriptor);
        $this->assertEquals($url, $router->makeUrl($name, $params));
    }

    public function provider()
    {
        return [
            [
                'name' => 'OrderCreate',
                'descriptor' => [
                    'allowed_methods'      => ['GET'],
                    'cleared_uri'          => 'order/create',
                    'required_param_count' => 0,
                    'optional_param_count' => 0,
                    'pattern'              => '/^(GET):\/order\/create(\/\w+){0,0}$/i',
                    'class'                => 'app\web\Order',
                    'method'               => 'create',
                ],
                [],
                'url' => Utils::baseUrl() . 'order/create'
            ],
            [
                'name' => 'OrderPrint',
                'descriptor' => [
                    'description'          => 'GET:/order/print?/%1[/%2] => app\web\Order::print',
                    'allowed_methods'      => ['GET'],
                    'cleared_uri'          => 'order/print',
                    'required_param_count' => 1,
                    'optional_param_count' => 1,
                    'pattern'              => '/^(GET):\/order\/print(\/\w+){1,2}$/i',
                    'class'                => 'app\web\Order',
                    'method'               => 'print',
                ],
                [10,20],
                'url' => Utils::baseUrl() . 'order/print/10/20'
            ],
        ];
    }
}