<?php

namespace DucCnzj\Ip\Imp;

/**
 * Interface CacheResponseImp
 *
 * @package DucCnzj\Ip\Imp
 */
interface CacheResponseImp
{
    /**
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCacheStore();

    /**
     * @param CacheStoreImp $cacheStore
     *
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function setCacheStore(CacheStoreImp $cacheStore);

    /**
     * @param string $name
     * @param string $ip
     *
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function cacheKey(string $name, string $ip);
}
