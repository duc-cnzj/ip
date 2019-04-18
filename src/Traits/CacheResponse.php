<?php

namespace DucCnzj\Ip\Traits;

use DucCnzj\Ip\CacheStore;
use DucCnzj\Ip\Imp\CacheStoreImp;

trait CacheResponse
{
    /**
     * @var null|CacheStoreImp
     */
    protected $cacheStore;

    /**
     * @return CacheStore|CacheStoreImp
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCacheStore()
    {
        if ($this->cacheStore instanceof CacheStoreImp) {
            return $this->cacheStore;
        }

        return $this->cacheStore = $this->getDefaultCacheDriver();
    }

    /**
     * @return CacheStore
     *
     * @author duc <1025434218@qq.com>
     */
    public function getDefaultCacheDriver()
    {
        return new CacheStore();
    }

    /**
     * @param CacheStoreImp $cacheStore
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setCacheStore(CacheStoreImp $cacheStore)
    {
        $this->cacheStore = $cacheStore;

        return $this;
    }

    /**
     * @param string $name
     * @param string $ip
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function cacheKey(string $name, string $ip)
    {
        return $name . ':' . $ip;
    }
}
