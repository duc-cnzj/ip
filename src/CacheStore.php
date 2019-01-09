<?php

namespace DucCnzj\Ip;

use DucCnzj\Ip\Imp\CacheStoreImp;

class CacheStore implements CacheStoreImp, \Countable
{
    /**
     * The array of stored values.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->storage[$key] ?? null;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array $keys
     *
     * @return array
     */
    public function many(array $keys)
    {
        $result = [];

        foreach ($keys as $key) {
            $result[] = $this->get($key);
        }

        return $result;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string    $key
     * @param  mixed     $value
     *
     * @return void
     */
    public function put($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array     $values
     *
     * @return void
     */
    public function putMany(array $values)
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value);
        }
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        unset($this->storage[$key]);

        return true;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->storage = [];

        return true;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return '';
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getAllItems(): array
    {
        return $this->storage;
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->storage);
    }
}
