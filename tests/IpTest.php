<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\IpClient;
use DucCnzj\Ip\Imp\IpImp;
use DucCnzj\Ip\DataMapper;
use DucCnzj\Ip\RequestHandler;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\TaobaoIp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use DucCnzj\Ip\Exceptions\InvalidIpAddress;
use DucCnzj\Ip\Exceptions\ServerErrorException;
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
        'ip'      => '127.0.0.1',
        'country' => '阿鲁巴',
        'region'  => '省市',
        'city'    => '地区',
        'address' => '中国浙江绍兴',
        'point_x' => '10.00',
        'point_y' => '20.00',
        'isp'     => '移动',
    ];

    protected function setUp(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $this->client = new IpClient();
    }

    /** @test */
    public function get_original_fail_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        // 模拟 ip 服务端获取失败的情况。
        $handler = \Mockery::mock(RequestHandlerImp::class);
        $exception = new ServerErrorException;
        $handler->shouldReceive('send')->andThrow($exception);

        $client->shouldReceive('setIp')->with($ip)->andReturn($client);
        $client->shouldReceive('getIp')->andReturn($ip);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);

        $this->assertEquals($ip, $client->getIp());
        $this->assertEquals($client, $client->setIp($ip));
        $this->assertEquals($handler, $client->getRequestHandler());

        $this->assertEquals([
            'success' => 0,
            'message' => $exception->getMessage(),
        ], $client->getOriginalInfo());
    }

    /** @test */
    public function get_original_success_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        // 模拟 ip 服务端获取失败的情况。
        $handler = \Mockery::mock(RequestHandlerImp::class);
        $handler->shouldReceive('send')->andReturn($this->data);

        $client->shouldReceive('setIp')->with($ip)->andReturn($client);
        $client->shouldReceive('getIp')->andReturn($ip);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);

        $this->assertEquals($ip, $client->getIp());
        $this->assertEquals($client, $client->setIp($ip));
        $this->assertEquals($handler, $client->getRequestHandler());

        $this->assertEquals($this->data, $client->getOriginalInfo());
    }

    /** @test */
    public function mock_client_try_test()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        $httpClient = \Mockery::mock(ClientInterface::class);
        $client->useProvider('taobao');

        $taobao = \Mockery::mock(TaobaoIp::class);
        $exception = new ServerErrorException;

        $taobao->expects()->send()->with($httpClient, $ip)->times(2)->andThrow($exception);
        $taobao->expects()->send()->with($httpClient, $ip)->times(1)->andReturn($this->data);

        // 模拟 ip 服务端获取失败的情况。
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();
        $handler->shouldReceive('getClient')->andReturn($httpClient);

        $client->shouldReceive('setIp')->with($ip)->andReturn($client);
        $client->shouldReceive('getIp')->andReturn($ip);
        $client->shouldReceive('getRequestHandler')->andReturn($handler);

        $client->shouldReceive('resolveProviders')->andReturn(['taobao' => $taobao]);

        $this->assertEquals(array_merge($this->data, [
            'provider' => 'taobao',
            'success'  => 1,
        ]), $client->getOriginalInfo());
    }

//    /** @test */
//    public function test_get_ip()
//    {
//        $ip = '117.149.174.132';
//        $client = new IpClient();
//        $client->useProvider('taobao')->setProviderConfig('baidu', ['ak' => 'swXuvzN8SoZeQUwcV1mtcMQhjAEMDyq5']);
//        $client->setIp($ip);
//        var_dump($client->getOriginalInfo());
//    }

    /** @test */
    public function bound_test()
    {
        $client = new IpClient();
        $taobao = \Mockery::mock(TaobaoIp::class);

        $client->bound('taobao', $taobao);

        $this->assertSame($client->getInstanceByName('taobao'), $taobao);
    }

    /** @test */
    public function get_ip_info()
    {
        $ip = '117.149.174.132';

        $client = \Mockery::mock(IpClient::class);

        $client->shouldReceive('setIp')->andReturn($client);

        $client->shouldReceive('getOriginalInfo')->andReturn($this->data);

        $client->shouldReceive('getCountry')->andReturn((new DataMapper($this->data))->getCountry());

        $this->assertEquals($this->data, $client->setIp($ip)->getOriginalInfo());

        $this->assertEquals('阿鲁巴', $client->setIp($ip)->getCountry());
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
        $this->assertEquals(3, $this->client->getRequestHandler()->tryTimes);

        $this->client->try(10);
        $this->assertEquals(10, $this->client->getRequestHandler()->tryTimes);
    }
}
