<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 28.09.2014
 * Time: 22:57
 */

namespace core;

/**
 * Class Router
 * @package core
 */
class Router
{
    private static $allowed_rest_methods = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * Array
     * (
     *   [editOrders] => Array (
     *     [request_methods] => Array (
     *       [0] => PUT
     *       [1] => POST
     *     )
     *     [request_uri] => /orders/edit
     *     [parameters] => Array (
     *       [id] => 17
     *       [group] => fruits
     *     )
     *     [class] => Order
     *     [action] => edit
     *   )
     * )
     * (
     * @request_methods array. Array whose elements are in the list $allowed_rest_methods. Ex.: ['GET', 'POST']
     * @request_uri string
     * @parameters array
     * @class string
     * @action string. Method of @class
     */
    private $routes = [];

    /**
     * @var string
     */
    private $controller_name = '';

    /**
     * @var string
     */
    private $method_name = '';

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var \core\generic\Controller
     */
    private $controller;

    public function __construct() {}

    /**
     * Add route to internal array
     * @param string $name
     * @param array $descriptor
     */
    public function addRoute($name, $descriptor)
    {
        $this->routes[$name] = $descriptor;
    }

    /**
     * Get all available REST methods
     * @return array
     */
    public static function getAllowedRestMethods()
    {
        return self::$allowed_rest_methods;
    }

    /**
     * Get all available routes
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Executes a method with the parameters defined in the URL
     * @throws \RuntimeException
     */
    public function execAction()
    {
        if (!method_exists($this->controller_name, $this->method_name)) {
            throw new \RuntimeException("Method {$this->method_name} in class {$this->controller_name} not exist", 500);
        }
        $this->checkParameters();
        $this->controller = new $this->controller_name;
        call_user_func_array([$this->controller, $this->method_name], $this->parameters);
    }

    /**
     * Looking for a suitable controller and method also defines the parameters of the method from URI
     * @throws \RuntimeException
     */
    public function getActionFromURI()
    {
        $script_path = str_replace(
            'index.php',
            '',
            str_replace('//index.php', '/index.php', $_SERVER['SCRIPT_NAME'])
        );
        $request_uri = str_replace($script_path, '', $_SERVER['REQUEST_URI']);
        $http_action = str_replace(['index.php?/', 'index.php?'], '', $request_uri);
        $action = strtoupper($_SERVER['REQUEST_METHOD']) . ':/' . $http_action;

        foreach($this->routes as $route => $description)
        {
            if (preg_match($this->createPattern($description), $action) > 0)
            {
                $this->controller_name  = $description['class'];
                $this->method_name = $description['action'];
                $this->parameters  = $description['parameters'];
                $this->parseParameters(
                    ltrim($http_action, '/'),
                    ltrim($description['request_uri'], '/')
                );
                return;
            }
        }
        throw new \RuntimeException('Unable to resolve the request ' . $action, 404);
    }

    /**
     * Looking for a suitable controller and method also defines the parameters of the method
     * from command line.
     * @throws \RuntimeException
     */
    public function getActionFromCommandLine()
    {
        if (isset($_SERVER['argv']) && count($_SERVER['argv']) >= 3)
        {
            $params = $_SERVER['argv'];
            $this->setControllerName($params[1]);
            $this->setMethodName($params[2]);
            array_shift($params);
            array_shift($params);
            array_shift($params);
            $method_params = [];
            foreach($params as $param)
            {
                $a = explode('=', $param);
                if (sizeof($a) < 2) {
                    App::failure(400, $this->makeErrorMessageForInvalidParamsCLI());
                }
                $method_params[$a[0]] = $a[1];
            }

            $this->setParameters($method_params);
        } else {
            $message = 'Not enough parameters.' . PHP_EOL . 'Syntax:'
                . ' php console.php controller methods [param1=value1 [ ... ]]' . PHP_EOL;
            App::failure(400, $message);
        }
    }

    /**
     * Create regexp pattern for matching with URL
     * @param array $description
     * @return string
     */
    private function createPattern($description)
    {
         // Examples:
         // $url = 'POST:/abcdef/1/aa';
         // $pattern = '/^(GET|POST):\/abcdef\/\w+\/\w+$/';
        $pattern = '/^(';
        for($i = 0; $i < count($description['request_methods']); $i++) {
            $pattern .= $description['request_methods'][$i] .
                (($i == (count($description['request_methods'])-1)) ? '' : '|');
        }
        $pattern .= '):' . str_replace('/', '\/', $description['request_uri']);
        for($i = 0; $i < count($description['parameters']); $i++) {
            $pattern .= '\w+' . (($i < (count($description['parameters']) - 1)) ? '\/' : '');
        }
        return ($pattern . '$/i');
    }

    /**
     * Extracts parameters from URL and fills the array with the parameters $this->parameters
     * @param string $http_action
     * @param string $request_uri
     */
    private function parseParameters($http_action, $request_uri)
    {
        $params = explode('/', substr($http_action, strlen($request_uri)));
        $i = 0;
        foreach($this->parameters as $param => $value)
        {
            $this->parameters[$param] = (isset($params[$i]) ? $params[$i] : '');
            $i++;
        }
    }

    /**
     * @return string
     */
    public function controllerName()
    {
        return $this->controller_name;
    }

    /**
     * @param string $controller_name
     */
    public function setControllerName($controller_name)
    {
        $this->controller_name = $controller_name;
    }

    /**
     * @return string
     */
    public function methodName()
    {
        return $this->method_name;
    }

    /**
     * @param $method_name
     */
    public function setMethodName($method_name)
    {
        $this->method_name = $method_name;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->controllerName() . '::' . $this->methodName();
    }

    /**
     * @return generic\Controller
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    private function checkParameters()
    {
        $method = new \ReflectionMethod($this->controllerName(), $this->methodName());
        $params = $method->getParameters();
        foreach($params as $param)
        {
            if (!$param->isOptional() && !isset($this->parameters[$param->getName()])) {
                throw new \RuntimeException($this->makeErrorMessageForInvalidParams(), 400);
            }
        }
    }

    private function makeErrorMessageForInvalidParams()
    {
        return Utils::isCLI()
            ? $this->makeErrorMessageForInvalidParamsCLI()
            : $this->makeErrorMessageForInvalidParamsWeb();
    }

    private function makeErrorMessageForInvalidParamsCLI()
    {
        $params = (new \ReflectionMethod($this->controllerName(), $this->methodName()))
            ->getParameters();
        $message = 'Not enough parameters.' . PHP_EOL . 'Syntax:' . PHP_EOL
            . 'php crystal.php '
            . $this->controllerName() . ' '
            . $this->methodName();
        /** @var \ReflectionParameter $param */
        foreach($params as $param) {
            if ($param->isOptional()) {
                $message .= ' [' . $param->getName() . '=value]';
            } else {
                $message .= ' ' . $param->getName() . '=value';
            }
        }
        return $message;
    }

    private function makeErrorMessageForInvalidParamsWeb()
    {
        $params = (new \ReflectionMethod($this->controllerName(), $this->methodName()))
            ->getParameters();
        $message = 'Required parameters: ';
        /** @var \ReflectionParameter $param */
        foreach($params as $param) {
            if ($param->isOptional()) {
                $message .= '[&' . $param->getName() . '=value]';
            } else {
                $message .= '&' . $param->getName() . '=value';
            }
        }
        return $message;
    }
}
