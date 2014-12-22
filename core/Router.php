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
    const VARIABLE_PARAM_COUNT = -1;
    const IGNORE_PARAMS        = -2;

    private static $restful_methods = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * @var array
     * Example:
     * $routes['OrderCreate'] = [
     *     'allowed_methods' => ['POST'], // Allowed methods in Router::$restful_methods.
     *     'request_uri'     => 'orders/create',
     *     'class'           => '\app\web\Order',
     *     'method'          => 'create'
     * ]
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
    public static function getRestfulMethods()
    {
        return self::$restful_methods;
    }

    /**
     * Executes a method with the parameters defined in the URL
     * @throws \RuntimeException
     */
    public function execAction()
    {
        if (!method_exists($this->controller_name, $this->method_name)) {
            throw new \RuntimeException("Method {$this->method_name} "
                . "in class {$this->controller_name} not exist", 500);
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
        $request_uri = str_replace(
            ['index.php?/', 'index.php?', 'index.php'],
            '',
            $_SERVER['REQUEST_URI']
        );
        $script_path = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        if ($request_uri === $script_path) {
            $action = '';
        } else {
            $action = trim(substr($request_uri, strlen($script_path)), '/');
        }
        $method_action = strtoupper($_SERVER['REQUEST_METHOD']) . ':' . $action;

        foreach($this->routes as $route => $description)
        {
            if (preg_match($description['pattern'], $method_action) > 0)
            {
                $this->controller_name = $description['class'];
                $this->method_name = $description['method'];
                if (($description['param_count'] === self::VARIABLE_PARAM_COUNT)
                    || ($description['param_count'] > 0))
                {
                    $this->parameters = $this->parseParameters($action, $description);
                } else {
                    $this->parameters = [];
                }
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
     * Extracts parameters from URL and returns array with parameters
     *
     * @param string $action
     * @param array $description
     * @return array
     */
    private function parseParameters($action, $description)
    {
        $params = [];
        if ($params_str = substr($action, strlen($description['request_uri'])))
        {
            $params = explode('/', trim($params_str, '/'));
        }
        return $params;
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
