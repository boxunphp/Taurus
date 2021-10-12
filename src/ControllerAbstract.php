<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Taurus;

use Taurus\Request\Request;
use Taurus\Response\Response;

/**
 * 控制器
 *
 * Class Controller
 * @package Alf
 */
abstract class ControllerAbstract
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;

    public function __construct()
    {
        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
    }

    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }
}
