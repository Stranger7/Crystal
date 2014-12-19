<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09.12.2014
 * Time: 21:54
 */

namespace core;

use core\generic\WebController;

class App
{
    const WEB_CONFIG_FILE           = 'app/config/web_config.ini';
    const CLI_CONFIG_FILE           = 'app/config/cli_config.ini';
    const VIEW_PATH                 = 'app/views';

    // Fail codes
    const INI_FILE_NOT_FOUND        = 1;
    const INI_FILE_NOT_PARSED       = 2;
    const LOGGER_NOT_DEFINED        = 3;
    const LOGGER_INVALID_FILENAME   = 4;
    const EXIT_USER_INPUT           = 6;

    /**
     * Application name
     * @var string
     */
    private static $name = '';

    /**
     * Mode application execution
     * This can be set to anything, but default usage is:
     *      development
     *      testing
     *      production
     *
     * @var string
     */
    private static $mode = '';

    /**
     * @var Config
     */
    private static $config;

    /**
     * @var Router
     */
    private static $router;

    /**
     * @var \core\generic\Logger
     */
    private static $logger;

    public static function init($name, $mode)
    {
        self::$name = $name;
        self::$mode = $mode;

        switch (self::$mode) {
            case 'testing':
            case 'production':
                error_reporting(0);
                ini_set("display_errors", 0);
                break;
            case 'development':
            default:
                error_reporting(E_ALL);
                ini_set("display_errors", 1);
                break;
        }

        self::$config = new Config(
            BASE_PATH . (Utils::isCLI() ? self::CLI_CONFIG_FILE : self::WEB_CONFIG_FILE)
        );
    }

    public static function run()
    {
        try {
            ob_start();

            self::$logger = Actuator::logger();
            self::$logger->start();

            Actuator::databases();

            self::$router = Actuator::router();
            if (Utils::isCLI()) {
                self::$router->getActionFromCommandLine();
            } else {
                self::$router->getActionFromURI();
            }

            self::$logger->setTitle(self::$router->getActionName());

            self::$router->execAction();

            ob_flush();
        } catch(\Exception $e) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            if (!Utils::isCLI())
            {
                /** @var WebController $controller */
                if (self::router() && ($controller = self::router()->controller())) {
                    $controller->http()->header($e->getCode());
                } else {
                    (new Http())->header($e->getCode());
                }
                echo $buffer;
                self::showError($error);
            } else {
                echo $buffer;
                echo "In file {$error['file']} at line ({$error['line']}) error ({$e->getCode()}) occurs: "
                    . $error['message'] . PHP_EOL;
            }
        } finally {
            self::$logger->stop();
        }
    }

    /**
     * @param int $code. See fail codes
     * @param string $message
     */
    public static function failure($code, $message = '')
    {
        echo "Fatal: Application failure with code ($code)" . (!empty($message) ? (': ' . $message) : '');
        exit($code);
    }

    /**
     * @return string
     */
    public static function name()
    {
        return self::$name;
    }

    /**
     * @return string
     */
    public static function mode()
    {
        return self::$mode;
    }

    /**
     * @param array $error
     */
    public static function showError($error)
    {
        self::view('common/error', $error);
    }

    /**
     * @return Config
     */
    public static function config()
    {
        return self::$config;
    }

    /**
     * @return \core\generic\Logger
     */
    public static function logger()
    {
        return self::$logger;
    }

    /**
     * @return Router
     */
    public static function router()
    {
        return self::$router;
    }

    /**
     * @param string $name
     * @param mixed $data
     * @return $this|string
     */
    public static function view($name, $data = [])
    {
        return (new View($name))->load($data);
    }
}