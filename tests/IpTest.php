<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use DucCnzj\Ip\IpClient;
use DucCnzj\Ip\Imp\IpImp;
use Mockery\MockInterface;
use DucCnzj\Ip\NullDataMapper;
use DucCnzj\Ip\RequestHandler;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\AliIp;
use DucCnzj\Ip\Imp\CacheStoreImp;
use DucCnzj\Ip\Strategies\BaiduIp;
use DucCnzj\Ip\Strategies\TaobaoIp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
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

    protected $taobaoReturn = '{"code":0,"data":{"ip":"117.149.174.132","country":"中国","area":"","region":"浙江","city":"绍兴","county":"XX","isp":"移动","country_id":"CN","area_id":"","region_id":"330000","city_id":"330600","county_id":"xx","isp_id":"100025"}}';

    /**
     * @var string
     */
    protected $baiduUrl = 'https://api.map.baidu.com/location/ip';

    /**
     * @var string
     */
    protected $aliUrl = 'http://iploc.market.alicloudapi.com/v3/ip';

    /**
     * @var string
     */
    protected $tencentUrl = 'https://apis.map.qq.com/ws/location/v1/ip';

    /**
     * @var string
     */
    protected $taobaoUrl = 'http://ip.taobao.com/service/getIpInfo.php';

    /**
     * @var array
     */
    protected $data = [
        'ip'       => '127.0.0.1',
        'country'  => '阿鲁巴',
        'region'   => '省市',
        'city'     => '地区',
        'address'  => '中国浙江绍兴',
        'point_x'  => '',
        'point_y'  => '',
        'isp'      => '移动',
        'success'  => 1,
        'provider' => 'taobao',
    ];

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
        'isp'      => '',
        'success'  => 1,
        'provider' => 'baidu',
    ];

    /**
     * @var array
     */
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
    public function get_ip_test()
    {
        $this->expectExceptionMessage('请先设置 ip');
        $this->client->getIp();
    }

    /** @test */
    public function get_provider_config_test()
    {
        $this->assertEquals([], $this->client->getProviderConfig('baidu'));

        $this->client->setProviderConfig('baidu', ['secret' => 'xxxxxxxx']);

        $this->assertSame($this->client, $this->client->setProviderConfig('baidu', ['secret' => 'xxxxxxxx']));

        $this->assertEquals(['secret' => 'xxxxxxxx'], $this->client->getProviderConfig('baidu'));
    }

    /** @test */
    public function set_multi_provider_configs_test()
    {
        $this->assertEquals([], $this->client->getProviderConfig('baidu'));
        $this->assertEquals([], $this->client->getProviderConfig('ali'));

        $this->client->setConfigs(['baidu' => ['secret' => 'xxxxxxxx'], 'ali' => 'alixxxxx']);

        $this->assertEquals(['secret' => 'xxxxxxxx'], $this->client->getProviderConfig('baidu'));
        $this->assertEquals('alixxxxx', $this->client->getProviderConfig('ali'));

        $this->assertEquals([
            'baidu' => ['secret' => 'xxxxxxxx'],
            'ali'   => 'alixxxxx',
        ], $this->client->getConfigs('ali', 'baidu'));

        $this->assertEquals([
            'baidu' => ['secret' => 'xxxxxxxx'],
            'ali'   => 'alixxxxx',
        ], $this->client->getConfigs());
    }

    /** @test */
    public function set_mapper_test()
    {
        $client = \Mockery::mock(IpClient::class)->makePartial();

        $client->shouldReceive('getOriginalInfo')->andReturn([
            'success' => 0,
            'message' => '',
        ]);

        $client->setIp('127.0.0.1');
        $client->setDataMapper(new NullDataMapper());
        $this->assertInstanceOf(NullDataMapper::class, $client->getDataMapper());
    }

    /** @test */
    public function set_request_handler_test()
    {
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();
        $this->client->setRequestHandler($handler);
        $this->assertInstanceOf(RequestHandlerImp::class, $this->client->getRequestHandler());
    }

    /** @test */
    public function set_request_handler_and_config_test()
    {
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();
        $this->client->setRequestHandler($handler);
        $this->assertInstanceOf(RequestHandlerImp::class, $this->client->getRequestHandler());
        $store = \Mockery::mock(CacheStoreImp::class);
        $this->assertSame($handler, $this->client->getRequestHandler());

        $this->client->setCacheStore($store);
        $this->assertSame($store, $this->client->getRequestHandler()->getCacheStore());
        $this->client->try(100);
        $this->assertEquals(100, $this->client->getRequestHandler()->getTryTimes());
    }

    /** @test */
    public function set_cache_store_test()
    {
        $store = \Mockery::mock(CacheStoreImp::class);
        $this->client->setCacheStore($store);
        $this->assertInstanceOf(CacheStoreImp::class, $this->client->getCacheStore());
    }

    /** @test */
    public function use_method_test()
    {
        $this->client->use('');
        $this->assertEquals(['baidu', 'ali', 'tencent', 'taobao'], $this->client->getProviders());

        $this->client->clearUse();
        $this->assertEquals([], $this->client->getProviders());

        $this->client->use('taobao');
        $this->assertEquals(['taobao'], $this->client->getProviders());

        $this->client->use('taobao', 'baidu', 'duc');
        $this->assertEquals(3, count($this->client->getProviders()));

        $this->client->useProvider('baidu', '', '', 'qwer');
        $this->assertEquals(4, count($this->client->getProviders()));

        $this->client->clearUse();
        $this->assertEquals([], $this->client->getProviders());
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
            'provider: taobao. 获取 ip 信息失败',
            'provider: taobao. 参数验证失败',
        ], $client->getErrors());
    }

    /** @test */
    public function get_original_from_cache()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        /** @var RequestHandlerImp $handler */
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();

        $taobao = \Mockery::mock(TaobaoIp::class)->makePartial();

        $taobao->shouldReceive('send')->andReturn($this->data);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);

        $client->setIp($ip)
            ->use('taobao')
            ->bound('taobao', $taobao);

        $this->assertEquals($ip, $client->getIp());

        $this->assertEquals(
            $this->data,
            $client->getOriginalInfo()
        );

        $this->assertEquals($this->data, $client->getCacheStore()->get($handler->cacheKey('taobao', $ip)));
    }

    /** @test */
    public function get_original_from_cache_use_two_provider()
    {
        $ip = '127.0.0.1';
        $client = \Mockery::mock(IpClient::class)->makePartial();

        /** @var RequestHandlerImp $handler */
        $handler = \Mockery::mock(RequestHandler::class)->makePartial();

        $taobao = \Mockery::mock(TaobaoIp::class)->makePartial();

        $taobao->shouldReceive('send')->andReturn($this->data);

        $baidu = \Mockery::mock(BaiduIp::class)->makePartial();

        $baidu->shouldReceive('send')->andReturn($this->data1);

        $client->shouldReceive('getRequestHandler')->andReturn($handler);

        $client->setIp($ip)
            ->bound('taobao', $taobao)
            ->bound('baidu', $baidu);

        $client->use('taobao');

        $this->assertEquals($ip, $client->getIp());

        $this->assertEquals(
            $this->data,
            $client->getOriginalInfo()
        );

        $this->assertEquals($this->data, $client->getCacheStore()->get($handler->cacheKey('taobao', $ip)));

        $client->clearUse()->use('baidu');
        $client->getOriginalInfo();
        $this->assertEquals($this->data1, $client->getCacheStore()->get($handler->cacheKey('baidu', $ip)));

        $this->assertCount(2, $client->getCacheStore());
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

        $this->assertEquals(4, count($instances));

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

        $this->assertEquals(['baidu', 'ali', 'tencent', 'taobao'], $providers);
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
        $taobao->expects()->send($httpClient, $ip1)->andReturn($this->taobaoData);

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
        $this->assertEquals($this->taobaoData, $client->getOriginalInfo());
    }

    /** @test */
    public function the_order_in_which_methods_are_called()
    {
        $ip = '127.0.0.1';
        /** @var IpClient|MockInterface $client */
        $client = \Mockery::mock(IpClient::class)->makePartial();
        $baidu = \Mockery::mock(BaiduIp::class);
        $ali = \Mockery::mock(AliIp::class);
        $taobao = \Mockery::mock(TaobaoIp::class);

        $exception = new ServerErrorException('获取失败');
        $ali->shouldReceive('send')->andThrow($exception);
        $taobao->shouldReceive('send')->andThrow($exception);
        $baidu->shouldReceive('send')->andThrow($exception);

        $client->use('ali', 'baidu', 'taobao');
        $client->setProviderConfig('ali', 'appcode');
        $client->setProviderConfig('baidu', 'ak');
        $client->bound('baidu', $baidu)
            ->bound('ali', $ali)
            ->bound('taobao', $taobao)
            ->try(1);

        $client->setIp($ip);

        $client->getOriginalInfo();
        $this->assertEquals([
            'provider: ali. 获取失败',
            'provider: baidu. 获取失败',
            'provider: taobao. 获取失败',
        ], $client->getErrors());
    }

    /** @test */
    public function test_send_use_all_provider()
    {
        $ip = '127.0.0.1';
        $guzzle = \Mockery::mock(Client::class);

        $request = \Mockery::mock(RequestInterface::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(401);

        $response->shouldReceive('getBody')->once()->andReturn(json_encode([
            'status'  => '400',
            'message' => 'baidu AK参数不存在',
        ]));

        $response->shouldReceive('getBody')->once()->andReturn(json_encode([
            'status'  => '400',
            'message' => 'tencent 缺少必要字段key',
        ]));

        $response->shouldReceive('getBody')->once()->andReturn($this->taobaoReturn);

        $requestHandler = \Mockery::mock(RequestHandler::class)->makePartial();

        $guzzle->shouldReceive('request')
            ->with('get', $this->baiduUrl . '?ip=' . $ip . '&ak=')
            ->andReturn($response);

        $guzzle->shouldReceive('request')->with(
            'get',
            $this->aliUrl . '?ip=' . $ip,
            [
                'headers' => [
                    'Authorization' => 'APPCODE ',
                ],
            ]
        )->andThrow(new ClientException('ali 401 fail', $request, $response));

        $guzzle->shouldReceive('request')
            ->with('get', $this->tencentUrl . '?ip=' . $ip . '&key=')
            ->andReturn($response);

        $guzzle->shouldReceive('request')
            ->with('get', $this->taobaoUrl . '?ip=' . $ip)
            ->andReturn($response);

        $requestHandler->shouldReceive('getClient')->andReturn($guzzle);

        $this->client->setRequestHandler($requestHandler);

        $city = $this->client->setIp($ip)->use('baidu', 'ali', 'tencent', 'taobao')->getCity();
        $this->assertEquals('绍兴', $city);
        $this->assertEquals([
            'provider: baidu. baidu AK参数不存在',
            'provider: ali. ali 401 fail',
            'provider: tencent. tencent 缺少必要字段key',
        ], $this->client->getErrors());
    }
}
