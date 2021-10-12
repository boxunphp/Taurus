<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Boxunphp\Taurus\Request;

trait BagTrait
{
    /**
     * 操作$_GET
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if ($key) {
            return $this->req->query->get($key, $default);
        } else {
            return $this->req->query->all();
        }
    }

    /**
     * 操作$_POST
     *
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        if ($key) {
            return $this->req->request->get($key, $default);
        } else {
            return $this->req->request->all();
        }
    }

    /**
     * 操作$_POST and $_GET
     *
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        if ($key) {
            return $this->post($key) ?? $this->get($key, $default);
        } else {
            return $this->post() + $this->get();
        }
    }

    /**
     * 操作$_SERVER
     *
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        if ($key) {
            return $this->req->server->get($key, $default);
        } else {
            return $this->req->server->all();
        }
    }

    /**
     * 操作$_COOKIE
     *
     * @return mixed
     */
    public function cookie($key = null, $default = null)
    {
        if ($key) {
            return $this->req->cookies->get($key, $default);
        } else {
            return $this->req->cookies->all();
        }
    }

    /**
     * 操作headers
     *
     * @return mixed
     */
    public function header($key = null, $default = null)
    {
        if ($key) {
            return $this->req->headers->get($key, $default);
        } else {
            return $this->req->headers->all();
        }
    }

    /**
     * 操作$_FILES
     *
     * @return mixed
     */
    public function file($key = null, $default = null)
    {
        if ($key) {
            return $this->req->files->get($key, $default);
        } else {
            return $this->req->files->all();
        }
    }

    /**
     * 附加属性
     *
     * @return mixed
     */
    public function attribute($key = null, $default = null)
    {
        if ($key) {
            return $this->req->attributes->get($key, $default);
        } else {
            return $this->req->attributes->all();
        }
    }

    /**
     * 添加附加属性
     *
     * @param array $attributes
     * @return self
     */
    public function setAttributes($attributes)
    {
        $this->req->attributes->add($attributes);
        return $this;
    }
}
