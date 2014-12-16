<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 12.12.2014
 * Time: 14:50
 */

namespace core;
use core\generic\WebController;

/**
 * Class Html
 * Only static methods
 * @package core
 */
class Html
{
    /**
     * Generate HTML code for hidden input for CSRF token
     * @throws \Exception
     */
    public static function CSRFToken()
    {
        if (App::router()->controller() instanceof WebController)
        {
            /** @var WebController $controller */
            $controller = App::router()->controller();
            echo '<input type="hidden" id="' . App::config()->get('security', 'csrf_token_name')
                . '" value="' . $controller->security()->getCsrfHash() . '" />' . PHP_EOL;
        }
    }

    public static function js($list = [])
    {
        foreach ($list as $js) {
            echo self::indent() . '<script type="application/javascript" src="' . $js . '"></script>' . PHP_EOL;
        }
    }

    public static function css($list = [])
    {
        foreach ($list as $css) {
            echo self::indent() . '<link rel="stylesheet" href="' . $css . '" type="text/css" />' . PHP_EOL;
        }
    }

    private static function indent($len = 4)
    {
        return sprintf("%{$len}s", ' ');
    }
}