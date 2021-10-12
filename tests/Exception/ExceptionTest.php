<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\Exception;

use Taurus\Exception\BadRequestException;
use Taurus\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    /**
     * @expectException  \Taurus\Exception\NotFoundException
     * @expectExceptionCode 404
     * @expectExceptionMessage Not Found
     */
    public function testNotFound()
    {
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not Found');
        throw new NotFoundException();
    }

    /**
     * @expectException \Taurus\Exception\BadRequestException
     * @expectExceptionCode 400
     * @expectExceptionMessage Bad Request
     */
    public function testBadRequest()
    {
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Bad Request');
        throw new BadRequestException();
    }
}
