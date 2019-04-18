<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\IpClient;
use DucCnzj\Ip\CacheStore;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Imp\CacheStoreImp;

/**
 * Class CacheStoreTest
 * @package DucCnzj\Ip\Tests
 */
class CacheStoreTest extends TestCase
{
    /**
     * @var CacheStoreImp
     */
    protected $store;

    /**
     *
     * @author duc <1025434218@qq.com>
     */
    protected function setUp()
    {
        parent::setUp();
        $this->store = new CacheStore();
    }

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

    /** @test */
    public function cache_store_test_many()
    {
        $this->assertEquals([null, null], $this->store->many(['duc', 'abc']));
    }

    /** @test */
    public function test_put_many()
    {
        $this->store->putMany(['name'=>'duc', 'age' => 18]);

        $this->assertCount(2, $this->store);
    }

    /** @test */
    public function test_get_all_items()
    {
        $this->store->putMany(['name'=>'duc', 'age' => 18]);

        $this->assertEquals(['name'=>'duc', 'age' => 18], $this->store->getAllItems());
    }
}
