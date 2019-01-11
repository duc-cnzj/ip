<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\CacheStore;
use DucCnzj\Ip\Imp\CacheStoreImp;
use DucCnzj\Ip\IpClient;
use DucCnzj\Ip\Imp\IpImp;
use DucCnzj\Ip\NullDataMapper;
use DucCnzj\Ip\RequestHandler;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\TaobaoIp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use DucCnzj\Ip\Exceptions\InvalidIpAddress;
use DucCnzj\Ip\Exceptions\ServerErrorException;
use DucCnzj\Ip\Exceptions\InvalidArgumentException;
use DucCnzj\Ip\Exceptions\IpProviderClassNotExistException;

class IpTest extends TestCase
{
    /**
     * @var IpClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $data = [
        'ip'       => '127.0.0.1',
        'country'  => '阿鲁巴',
        'region'   => '省市',
        'city'     => '地区',
        'address'  => '中国浙江绍兴',
        'point_x'  => '10.00',
        'point_y'  => '20.00',
        'isp'      => '移动',
        'success'  => 1,
        'provider' => 'taobao',
    ];

    /**
     * @var array
     */
    protected $data1 = [
        'ip'       => '127.0.0.2',
        'country'  => '芭芭拉',
        'region'   => '墨兰',
        'city'     => '次序',
        'address'  => '芭芭拉墨兰次序',
        'point_x'  => '4000',
        'point_y'  => '3000',
        'isp'      => '电信',
        'success'  => 1,
        'provider' => 'taobao',
    ];

    protected $errors = [
        'success' => 0,
        'message' => '获取 ip 信息失败',
    ];

    protected function setUp(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $this->client = new IpClient();
    }
    
    /** @test */
    function get_ip_test()
    {
        $this->expectExceptionMessage('请先设置 ip');
        $this->client->getIp();
    }

    /** @test */
    function get_provider_config_test()
    {
        $this->assertEquals([], $this->client->getProviderConfig('baidu'));

        $this->client->setProviderConfig('baidu', ['secret' => 'xxxxxxxx']);

        $this->assertSame($this->client, $this->client->setProviderConfig('baidu', ['secret' => 'xxxxxxxx']));

        $this->assertEquals(['secret' => 'xxxxxxxx'], $this->client->getProviderConfig('baidu'));
    }

    /** @test */
    function set_mapper_test()
    {
        $this->client->setIp('127.0.0.1');
        $this->client->setDataMapper(new NullDataMapper());
        $this->assertInstanceOf(NullDataMapper::class, $this->client->getDataMapper());
    }

    /** @test */
    function set_request_handler_test()
    {
        $handler = \Mockery::mock(RequestHandlerImp::class);
        $this->client->setRequestHandler($handler);
        $this->assertInstanceOf(RequestHandlerImp::class, $this->client->getRequestHandler());
    }
    
    /** @test */
    function set_cache_store_test()
    {
        $store = \Mockery::mock(CacheStoreImp::class);
        $this->client->setCacheStore($store);
        $this->assertInstanceOf(CacheStoreImp::class, $this->client->getCacheStore());
    }

    /** @test */
    function use_method_test()
    {
        $this->client->use('taobao');
        $this->assertEquals(['taobao'], $this->client->getProviders());

        $this->client->useProvider('baidu');
        $this->assertEquals(['taobao', 'baidu'], $this->client->getProviders());
    }

    /** @test */
    public function test_get_errors()
    {
        $ip = '127.0.0.1';

        $client = \Mockery::mock(IpClient::class)->makePartial();
        $httpClient = \Mockery::mock(ClientInterface::class);

        /** @var TaobaoIp $taobao */
        $taobao = \Mockery::mock(TaobaoIp::class);

        /** @var RequestHandler $handler */
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();

        $exception = new InvalidArgumentException();
        $exception1 = new ServerErrorException();

        $taobao->expects()->send($httpClient, $ip)->times(1)->andThrow($exception1);
        $taobao->expects()->send($httpClient, $ip)->times(1)->andThrow($exception);

        $handler->shouldReceive('getClient')->andReturn($httpClient);

        $client->setIp($ip)->bound('taobao', $taobao)->useProvider('taobao');

        $client->shouldReceive('getRequestHandler')->andReturn($handler);
        $client->shouldReceive('getCity')->andReturn($client->getDataMapper()->getCity());

        $client->getCity();

        $this->assertEquals([
            '获取 ip 信息失败',
            '参数验证失败',
        ], $client->getErrors());
    }

    /** @test */
    public function get_original_from_cache()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        $cacheStore = \Mockery::mock(CacheStore::class)->makePartial();

        /** @var RequestHandlerImp $handler */
        $handler = \Mockery::mock(RequestHandlerImp::class);

        // 模拟 ip 服务端获取失败的情况。
        $handler->shouldReceive('send')->andReturn($this->data);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);
        $client->shouldReceive('getCacheStore')->andReturn($cacheStore);

        $client->setIp($ip);
        $this->assertEquals($ip, $client->getIp());

        $this->assertEquals(
            $this->data,
            $client->getOriginalInfo()
        );

        $this->assertEquals($this->data, $client->getCacheStore()->get($ip));
    }

    /** @test */
    public function get_original_fail_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();
        $handler = \Mockery::mock(RequestHandlerImp::class);

        $exception = new ServerErrorException;

        // 模拟 ip 服务端获取失败的情况。
        $handler->shouldReceive('send')->andThrow($exception);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);

        $client->setIp($ip);

        $this->assertEquals($ip, $client->getIp());
        $this->assertEquals($handler, $client->getRequestHandler());

        $this->assertEquals(
            [
                'success' => 0,
                'message' => $exception->getMessage(),
            ],
            $client->getOriginalInfo()
        );
    }

    /** @test */
    public function get_original_and_map_success_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        $client->shouldReceive('getOriginalInfo')->andReturn($this->data);

        $client->shouldReceive('getAddress')->andReturn(
            $client->getDataMapper()
                ->getAddress()
        );
        $client->shouldReceive('getCity')->andReturn($client->getDataMapper()->getCity());
        $client->setIp($ip);

        $this->assertEquals($ip, $client->getIp());

        $this->assertEquals($this->data, $client->getOriginalInfo());
        $this->assertEquals('地区', $client->getCity());
        $this->assertEquals('中国浙江绍兴', $client->getAddress());
    }

    /** @test */
    public function get_original_and_map_fail_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        $client->shouldReceive('getOriginalInfo')->andReturn($this->errors);

        $client->setIp($ip);

        $client->shouldReceive('getAddress')->andReturn(
            $client->getDataMapper()
                ->getAddress()
        );
        $client->shouldReceive('getCity')->andReturn($client->getDataMapper()->getCity());

        $this->assertEquals($ip, $client->getIp());

        $this->assertEquals($this->errors, $client->getOriginalInfo());
        $this->assertInstanceOf(NullDataMapper::class, $client->getDataMapper());
        $this->assertNull($client->getCity());
        $this->assertNull($client->getAddress());
    }

    /** @test */
    public function mock_client_try_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();
        $httpClient = \Mockery::mock(ClientInterface::class);
        $taobao = \Mockery::mock(TaobaoIp::class);

        /** @var RequestHandler $handler */
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();

        $exception = new ServerErrorException;

        $taobao->expects()->send($httpClient, $ip)->times(3)->andThrow($exception);
        $taobao->expects()
            ->send()
            ->with($httpClient, $ip)
            ->once()
            ->andReturn($this->data);

        // 模拟 ip 服务端获取失败的情况。
        $handler->shouldReceive('getClient')->andReturn($httpClient);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);

        $client->setIp($ip)->useProvider('taobao')->bound('taobao', $taobao);

        $this->assertEquals(
            [
                'message' => '获取 ip 信息失败',
                'success' => 0,
            ],
            $client->getOriginalInfo()
        );
    }

    /** @test */
    public function mock_client_try_times_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        /** @var RequestHandler $handler */
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();
        $httpClient = \Mockery::mock(ClientInterface::class);
        $taobao = \Mockery::mock(IpImp::class);

        $exception = new ServerErrorException;
        $taobao->expects()->send($httpClient, $ip)->times(9)->andThrow($exception);
        $taobao->expects()
            ->send()
            ->with($httpClient, $ip)
            ->once()
            ->andReturn($this->data);

        // 模拟 ip 服务端获取失败的情况。
        $handler->shouldReceive('getClient')->andReturn($httpClient);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);
        $client->useProvider('taobao')->bound('taobao', $taobao);

        $client->setIp($ip)->try(3);
        $this->assertEquals(
            [
                'success' => 0,
                'message' => '获取 ip 信息失败',
            ],
            $client->getOriginalInfo()
        );

        $client->setIp($ip)->try(7);
        $this->assertEquals($this->data, $client->getOriginalInfo());
    }

    /** @test */
    public function null_object_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        $client->setIp($ip);

        $client->shouldReceive('getOriginalInfo')->andReturn($this->errors);

        $client->shouldReceive('getCity')->andReturn($client->getDataMapper()->getCity());
        $client->shouldReceive('getDuc')->andReturn($client->getDataMapper()->getDuc());
        $client->shouldReceive('getDuc')->andReturn($client->getDataMapper()->getDuc());

        $this->assertInstanceOf(NullDataMapper::class, $client->getDataMapper());

        $this->assertEquals('', $client->getCity());
        $this->assertEquals('', $client->getDuc());
    }

    /** @test */
    public function bound_test()
    {
        $client = new IpClient();
        $taobao = \Mockery::mock(TaobaoIp::class);

        $client->bound('taobao', $taobao);

        $instances = $client->resolveProviders();

        $this->assertEquals(2, count($instances));

        $this->assertSame($taobao, $instances['taobao']);
    }

    /** @test */
    public function map_data_success_test()
    {
        $ip = '117.149.174.132';

        $client = \Mockery::mock(IpClient::class)->makePartial();

        $client->shouldReceive('setIp')->with($ip)->andReturn($client);

        $client->shouldReceive('getOriginalInfo')->andReturn($this->data);

        $client->shouldReceive('getCountry')
            ->andReturn($client->getDataMapper()->getCountry());

        $client->setIp($ip);

        $this->assertEquals($this->data, $client->getOriginalInfo());

        $this->assertEquals('阿鲁巴', $client->getCountry());
    }

    /** @test */
    public function test_get_providers()
    {
        $client = new IpClient();

        $providers = $client->getProviders();

        $this->assertEquals(['baidu', 'taobao'], $providers);
    }

    /** @test */
    public function test_resolve_providers()
    {
        $client = new IpClient();

        $instances = $client->resolveProviders();

        foreach ($instances as $instance) {
            $this->assertInstanceOf(IpImp::class, $instance);
        }
    }

    /** @test */
    public function test_bound_providers()
    {
        $client = new IpClient();

        $taobao = new TaobaoIp();
        $client->useProvider('taobao');
        $this->assertEquals(1, count($client->getProviders()));

        $client->bound('taobao', $taobao);
        $this->assertSame($client->getInstanceByName('taobao'), $taobao);
    }

    /** @test */
    public function test_class_not_resolve_exception()
    {
        $this->expectException(IpProviderClassNotExistException::class);
        $client = new IpClient();
        $client->useProvider('duc');
        $client->resolveProviders();
    }

    /** @test */
    public function check_ip_fail_test()
    {
        $this->expectException(InvalidIpAddress::class);
        $this->client->checkIp('123456789');
    }

    /** @test */
    public function check_ip_success_test()
    {
        $this->client->setIp('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->client->getIp());
    }

    /** @test */
    public function try_times_test()
    {
        $this->assertEquals(3, $this->client->getRequestHandler()->getTryTimes());

        $this->client->try(10);
        $this->assertEquals(10, $this->client->getRequestHandler()->getTryTimes());
    }

    /** @test */
    public function change_ip_test()
    {
        $ip = '127.0.0.1';

        $ip1 = '127.0.0.2';
        $client = \Mockery::mock(IpClient::class)->makePartial();
        $exception = new ServerErrorException;

        /** @var RequestHandler $handler */
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();
        $httpClient = \Mockery::mock(ClientInterface::class);
        $taobao = \Mockery::mock(TaobaoIp::class);

        $taobao->expects()->send($httpClient, $ip)->times(8)->andThrow($exception);
        $taobao->expects()->send($httpClient, $ip)->andReturn($this->data);
        $taobao->expects()->send($httpClient, $ip1)->andReturn($this->data1);

        // 模拟 ip 服务端获取失败的情况。
        $handler->shouldReceive('getClient')->andReturn($httpClient);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);
        $client->useProvider('taobao')->bound('taobao', $taobao);

        $client->setIp($ip)->try(20);
        $this->assertEquals(
            $this->data,
            $client->getOriginalInfo()
        );

        $client->setIp($ip1);
        $this->assertEquals($this->data1, $client->getOriginalInfo());
    }
}
