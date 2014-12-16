<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 26.09.2014
 * Time: 22:48
 */

namespace core\generic;

use core\Http;
use core\Request;
use core\Security;

/**
 * Abstract class WebApp
 * @package core\generic
 */
abstract class WebController extends Controller
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var \core\Http
     */
    private $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Http();
        $this->security = new Security();
        $this->request = new Request($this->security);
    }

    /**
     * @return Security
     */
    public function security()
    {
        if (empty($this->security)) {
            throw new \RuntimeException('Security object not initialized', 500);
        }
        return $this->security;
    }

    /**
     * @return Request
     */
    public function request()
    {
        if (empty($this->request)) {
            throw new \RuntimeException('Request object not initialized', 500);
        }
        return $this->request;
    }

    /**
     * @return Http
     */
    public function http()
    {
        return $this->http;
    }
}