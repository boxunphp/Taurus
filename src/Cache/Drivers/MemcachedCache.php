<?php

/**
 * This file is part of the Boxunsoft package.
 *
 * (c) Jordy <arno.zheng@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Taurus\Cache\Drivers;

use Taurus\Cache\CacheInterface;
use Taurus\Instance\InstanceTrait;
use Taurus\Memcached\Memcached;

class MemcachedCache implements CacheInterface
{
    use InstanceTrait {
        getInstance as private _getInstance;
    }

    protected $mc;

    private function __construct(array $config)
    {
        $this->mc = Memcached::getInstance($config);
    }

    public static function getInstance(array $config)
    {
        return self::_getInstance($config);
    }

    public function set($key, $value, $expiration = 0)
    {
        return $this->mc->set($key, $value, $expiration);
    }

    public function get($key)
    {
        return $this->mc->get($key);
    }

    public function delete($key)
    {
        return $this->mc->delete($key);
    }

    public function setMulti(array $items, $expiration = 0)
    {
        return $this->mc->setMulti($items, $expiration);
    }

    public function getMulti(array $keys)
    {
        $result = $this->mc->getMulti($keys);
        if (!$result) {
            $result = [];
        }
        return $result;
    }

    public function deleteMulti(array $keys)
    {
        $result = $this->mc->deleteMulti($keys);
        if ($result && is_array($result)) {
            foreach ($result as $res) {
                if ($res !== true && $res != \Memcached::RES_NOTFOUND) {
                    return false;
                }
            }
        }
        return true;
    }
}
