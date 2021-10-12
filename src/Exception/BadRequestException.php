<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Boxunphp\Taurus\Exception;

class BadRequestException extends Exception
{
    protected $code = 400;
    protected $message = 'Bad Request';
}
