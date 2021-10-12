<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Taurus\Cache;

use Taurus\Cache\Drivers\ApcuCache;
use Taurus\Cache\Drivers\FileCache;
use Taurus\Cache\Drivers\MemcachedCache;
use Taurus\Cache\Drivers\RedisCache;
use Taurus\Exception\ErrorException;
use Taurus\Instance\InstanceTrait;

abstract class CacheAbstract
{
    use InstanceTrait;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * 缓存类型
     * @var int
     */
    protected $type = Cache::TYPE_MEMCACHED;
    /**
     * 配置文件的key
     * @var string
     */
    protected $configKey = '';
    /**
     * 配置信息,如果有定义了path和key,会被覆盖
     * @var array|null
     */
    protected $config = [];
    /**
     * 缓存前缀
     * @var string
     */
    protected $prefixKey = '';
    /**
     * 过期时间
     *
     * @var integer
     */
    protected $ttl = 0;

    /**
     * CacheAbstract constructor.
     * @throws ErrorException
     */
    public function __construct()
    {
        if ($this->configKey) {
            // 必须在全局定义环境函数, 用于获取配置
            if (!function_exists('env')) {
                throw new ErrorException('function env cannot be defined!', E_ERROR);
            }

            $this->config = env($this->configKey);
        }

        switch ($this->type) {
            case Cache::TYPE_MEMCACHED:
                $this->cache = MemcachedCache::getInstance($this->config);
                break;
            case Cache::TYPE_REDIS:
                $this->cache = RedisCache::getInstance($this->config);
                break;
            case Cache::TYPE_APCU:
                $this->cache = ApcuCache::getInstance($this->config);
                break;
            case Cache::TYPE_FILE:
                $this->cache = FileCache::getInstance($this->config);
                break;
            default:
                $this->cache = MemcachedCache::getInstance($this->config);
                break;
        }
    }

    public function set($key, $value, $expiration = 0)
    {
        $expiration = $expiration ?: ($this->ttl ?: 0);
        return $this->cache->set($this->prefixKey . $key, $value, $expiration);
    }

    public function get($key)
    {
        return $this->cache->get($this->prefixKey . $key);
    }

    public function delete($key)
    {
        return $this->cache->delete($this->prefixKey . $key);
    }

    public function setMulti(array $items, $expiration = 0)
    {
        $expiration = $expiration ?: ($this->ttl ?: 0);
        $newItems = [];
        foreach ($items as $key => $value) {
            $newItems[$this->prefixKey . $key] = $value;
        }
        return $this->cache->setMulti($newItems, $expiration);
    }

    public function getMulti(array $keys)
    {
        $newKeys = [];
        foreach ($keys as $idx => $key) {
            $newKeys[$idx] = $this->prefixKey . $key;
        }
        $result = $this->cache->getMulti($newKeys);
        $data = [];
        foreach ($newKeys as $idx => $key) {
            if (isset($result[$key])) {
                $data[$keys[$idx]] = $result[$key];
            }
        }
        return $data;
    }

    public function deleteMulti(array $keys)
    {
        $newKeys = [];
        foreach ($keys as $key) {
            $newKeys[] = $this->prefixKey . $key;
        }
        return $this->cache->deleteMulti($newKeys);
    }
}
