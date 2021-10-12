<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\Cache\Drivers;

use Boxunphp\Taurus\Cache\Drivers\RedisCache;
use PHPUnit\Framework\TestCase;

class RedisCacheTest extends TestCase
{
    /**
     * @var RedisCache
     */
    protected $cache;

    protected function setUp(): void
    {
        $config = [
            'master' => [
                'host' => 'redis-6379',
                'port' => 6379
            ],
        ];
        $this->cache = RedisCache::getInstance($config);
    }

    public function testCache()
    {
        $k1 = 'a';
        $k2 = 'b';
        $v1 = 1;
        $v2 = 2;
        $this->assertTrue($this->cache->set($k1, $v1));
        $this->assertEquals($v1, $this->cache->get($k1));
        $this->assertTrue($this->cache->delete($k1));
        $data = [$k1 => $v1, $k2 => $v2];
        $this->assertTrue($this->cache->setMulti($data));
        $this->assertEquals($data, $this->cache->getMulti([$k1, $k2]));
        $this->assertTrue($this->cache->deleteMulti([$k1, $k2]));
    }
}
