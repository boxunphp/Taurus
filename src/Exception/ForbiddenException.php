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

class ForbiddenException extends Exception
{
    protected $code = 403;
    protected $message = 'Forbidden';
}
