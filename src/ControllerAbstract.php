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

use Taurus\Traits\RequestTrait;
use Taurus\Traits\ResponseTrait;

/**
 * 控制器
 *
 * Class Controller
 */
abstract class ControllerAbstract
{
    use RequestTrait;
    use ResponseTrait;
}
