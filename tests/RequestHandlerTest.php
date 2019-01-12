<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use DucCnzj\Ip\CacheStore;
use DucCnzj\Ip\RequestHandler;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Imp\CacheStoreImp;
use DucCnzj\Ip\Strategies\TaobaoIp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Exception\ConnectException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

class RequestHandlerTest extends TestCase
{
    protected $ip = '127.0.0.1';

    /**
     * @var array
     */
    protected $taobaoData = [
        'ip'       => '127.0.0.1',
        'country'  => '阿西吧',
        'region'   => '洗吧',
        'city'     => '囖咯',
        'address'  => '中国浙江绍兴',
        'point_x'  => '',
        'point_y'  => '',
        'isp'      => '移动',
        'success'  => 1,
        'provider' => 'taobao',
    ];

    /**
     * @var RequestHandlerImp
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();
        $this->handler = new RequestHandler();
    }

    /** @test */
    public function get_handler_test()
    {
        $handler = new RequestHandler();

        $this->assertInstanceOf(Client::class, $handler->getClient());

        $this->assertSame($handler->getClient(), $handler->getClient());
    }

    /** @test */
    public function test_try_times()
    {
        $handler = new RequestHandler();

        $handler->setTryTimes(10);

        $this->assertEquals(10, $handler->getTryTimes());
    }

    /** @test */
    public function break_loop_test()
    {
        $this->expectException(ServerErrorException::class);

        $request = \Mockery::mock(RequestInterface::class);
        $taobao = \Mockery::mock(TaobaoIp::class);
        $connectExecption = new ConnectException('网络连接失败', $request);
        $taobao->shouldReceive('send')->andThrow($connectExecption);
        $providers = ['taobao' => $taobao];

        $this->handler->send($providers, $this->ip);
        $this->assertEquals(['网络连接失败'], $this->handler->getErrors());
    }

    /** @test */
    public function test_use_cache_result()
    {
        $ip = '127.0.0.1';
        $taobao = \Mockery::mock(TaobaoIp::class);
        $taobao->shouldReceive('send')->andReturn($this->taobaoData);

        $providers = [
            'taobao' => $taobao,
        ];
        /** @var CacheStoreImp $cacheStore */
        $cacheStore = $this->handler->getCacheStore();
        $this->assertEquals([], $cacheStore->getAllItems());

        $this->handler->send($providers, $ip);

        $this->assertCount(1, $cacheStore);

        $this->assertEquals($this->handler->send($providers, $ip), $cacheStore->get($this->handler->cacheKey('taobao', $ip)));
    }

    /** @test */
    public function test_use_cache_result_fail()
    {
        $this->expectException(ServerErrorException::class);

        $ip = '127.0.0.1';
        $cacheStore = \Mockery::mock(CacheStore::class)->makePartial();
        $cacheStore->shouldReceive('get')->with('taobao:' . $ip)->andReturnNull();

        $cacheStore->shouldReceive('put');

        $taobao = \Mockery::mock(TaobaoIp::class);

        $taobao->shouldReceive('send')->once()->andReturn($this->taobaoData);
        $taobao->shouldReceive('send')->once()->andThrow(new \Exception());

        $providers = [
            'taobao' => $taobao,
        ];

        $this->handler->setCacheStore($cacheStore);
        $this->assertSame($cacheStore, $this->handler->getCacheStore());

        $cacheStore = $this->handler->getCacheStore();
        $this->assertEquals([], $cacheStore->getAllItems());

        $this->handler->send($providers, $ip);
        $this->assertCount(0, $cacheStore);

        $this->handler->send($providers, $ip);
    }
}
