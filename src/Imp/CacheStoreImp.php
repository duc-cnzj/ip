<?php


namespace DucCnzj\Ip\Imp;


interface CacheStoreImp
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key);

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys);

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function put($key, $value);

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @return void
     */
    public function putMany(array $values);

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key);

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush();

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix();

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getAllItems(): array;
}