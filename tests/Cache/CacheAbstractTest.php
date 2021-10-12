<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\Cache;

use Taurus\Cache\Cache;
use Taurus\Cache\CacheAbstract;
use PHPUnit\Framework\TestCase;

class CacheAbstractTest extends TestCase
{
    public function testCacheConfig()
    {
        $cache = CacheConfig::getInstance();

        $k1 = 'a';
        $k2 = 'b';
        $v1 = 1;
        $v2 = 2;
        $this->assertTrue($cache->set($k1, $v1));
        $this->assertEquals($v1, $cache->get($k1));
        $this->assertTrue($cache->delete($k1));
        $data = [$k1 => $v1, $k2 => $v2];
        $this->assertTrue($cache->setMulti($data));
        $this->assertEquals($data, $cache->getMulti([$k1, $k2]));
        $this->assertTrue($cache->deleteMulti([$k1, $k2]));
    }

    public function testCacheFile()
    {
        $cache = CacheFile::getInstance();

        $k1 = 'a';
        $k2 = 'b';
        $v1 = 1;
        $v2 = 2;
        $this->assertTrue($cache->set($k1, $v1));
        $this->assertEquals($v1, $cache->get($k1));
        $this->assertTrue($cache->delete($k1));
        $data = [$k1 => $v1, $k2 => $v2];
        $this->assertTrue($cache->setMulti($data));
        $this->assertEquals($data, $cache->getMulti([$k1, $k2]));
        $this->assertTrue($cache->deleteMulti([$k1, $k2]));
    }
}

class CacheConfig extends CacheAbstract
{
    protected $cacheType = Cache::TYPE_MEMCACHED;
    protected $config = [
        'servers' => [
            ['host' => 'memcached-11211', 'port' => 11211]
        ],
        'connect_timeout' => 1000
    ];
    protected $prefixKey = 'testc';
    protected $ttl = 10;
}

class CacheFile extends CacheAbstract
{
    protected $type = Cache::TYPE_MEMCACHED;
    protected $configKey = 'mc/default';
    protected $prefixKey = 'testb';
    protected $ttl = 20;
}
