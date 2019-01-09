<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\IpClient;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Imp\CacheStoreImp;

class CacheStoreTest extends TestCase
{
    /** @test */
    public function cache_store_test()
    {
        $client = new IpClient();

        $cacheStore = $client->getCacheStore();

        $this->assertInstanceOf(CacheStoreImp::class, $cacheStore);

        $cacheStoreTwo = $client->getCacheStore();

        $this->assertSame($cacheStore, $cacheStoreTwo);
    }

    /** @test */
    public function cache_store_test_put_and_get()
    {
        $client = new IpClient();

        $cacheStore = $client->getCacheStore();

        $cacheStore->put('duc', 'cool');

        $this->assertEquals('cool', $cacheStore->get('duc'));
    }

    /** @test */
    public function cache_store_test_forget()
    {
        $client = new IpClient();

        $cacheStore = $client->getCacheStore();

        $cacheStore->put('duc', 'cool');

        $this->assertEquals('cool', $cacheStore->get('duc'));

        $cacheStore->forget('duc');

        $this->assertNull($cacheStore->get('duc'));
    }

    /** @test */
    public function cache_store_test_flush()
    {
        $client = new IpClient();

        $cacheStore = $client->getCacheStore();

        $cacheStore->put('duc', 'cool');
        $cacheStore->put('abc', 'cool');
        $cacheStore->put('bbc', 'cool');

        $this->assertCount(3, $cacheStore);
        $cacheStore->flush();

        $this->assertCount(0, $cacheStore);
    }

    /** @test */
    public function cache_store_test_get_prefix()
    {
        $client = new IpClient();

        $cacheStore = $client->getCacheStore();

        $this->assertEquals('', $cacheStore->getPrefix());
    }
}
