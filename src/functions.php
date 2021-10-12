<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

if (!function_exists('kernel')) {
    /**
     * @return Boxunphp\Taurus\Kernel
     */
    function kernel()
    {
        return \Boxunphp\Taurus\Kernel::getInstance();
    }
}

if (!function_exists('env')) {
    /**
     * @param string $key
     * @return array|null
     */
    function env($key)
    {
        return kernel()->env()->get($key);
    }
}

if (!function_exists('config')) {
    /**
     * @param string $key
     * @return array|null
     */
    function config($key)
    {
        return kernel()->config()->get($key);
    }
}

if (!function_exists('logger')) {
    /**
     * @return \Boxunphp\Taurus\Logger\Logger
     */
    function logger()
    {
        return kernel()->logger();
    }
}

if (!function_exists('is_email')) {
    function is_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }
}

if (!function_exists('is_phone')) {
    function is_phone($phone)
    {
        return preg_match("/^1(3[0-9]|8[0-9]|5[0-9]|7[0135678]|47|66|9[89])\d{8}$/", $phone);
    }
}

if (!function_exists('hide_phone')) {
    /**
     * 隐藏手机号码中间位
     */
    function hide_phone($phone)
    {
        if (empty($phone) || strlen($phone) != 11) {
            return $phone;
        }

        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }
}
