<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\TencentIp;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

class TencentIpTest extends TestCase
{
    /**
     * @var string
     */
    protected $url = 'https://apis.map.qq.com/ws/location/v1/ip';

    /**
     * @var string
     */
    protected $successReturn = '{
        "status":0,
        "message":"query ok",
        "result":{
            "ip":"202.106.0.30",
            "location":{
                "lng":116.407526,
                "lat":39.90403
            },
            "ad_info":{
                "nation":"中国",
                "province":"",
                "city":"",
                "adcode":110000
            }
        }
    }';

    /**
     * @var string
     */
    protected $failReturn = '
    {
        "status": 301,
        "message": "缺少必要字段key"
    }';

    /**
     * @var TencentIp
     */
    protected $server;

    /**
     *
     * @author duc <1025434218@qq.com>
     */
    protected function setUp()
    {
        parent::setUp();
        $this->server = new TencentIp();
    }

    /** @test */
    public function test_tencent_config()
    {
        $this->server->setConfig('xxxxx');
        $this->assertEquals('xxxxx', $this->server->getKey());

        $this->server->setConfig('');
        $this->assertEquals('', $this->server->getKey());

        $this->server->setConfig(['key' => 'xxx']);
        $this->assertEquals('xxx', $this->server->getKey());
    }

    /** @test */
    public function test_tencent_fail_send()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);

        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $guzzleClient->shouldReceive('request')->with(
            'get',
            $this->url . '?ip=' . $ip . '&key='
        )->andReturn($response);

        $response->shouldReceive('getBody')->andReturn($this->failReturn);
        $this->server->send($guzzleClient, $ip);
    }

    /** @test */
    public function test_tencent_success_send()
    {
        $ip = '127.0.0.1';

        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);

        $guzzleClient->shouldReceive('request')->with(
            'get',
            $this->url . '?ip=' . $ip . '&key=xxx'
        )->andReturn($response);

        $response->shouldReceive('getBody')->andReturn($this->successReturn);

        $this->server->setConfig('xxx');

        $this->assertEquals(
            $this->server->formatResult(['ip' => $ip], json_decode($this->successReturn, true)),
            $this->server->send($guzzleClient, $ip)
        );
    }

    /** @test */
    public function test_client_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);
        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);

        $e = \Mockery::mock(ClientException::class);
        $e->shouldReceive('getResponse')->andReturn($response);

        $response->shouldReceive('getStatusCode')->andReturn(400);

        $guzzleClient->shouldReceive('request')->with(
            'get',
            $this->url . '?ip=' . $ip . '&key='
        )->andThrow($e);

        $this->server->send($guzzleClient, $ip);
    }

    /** @test */
    public function test_server_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);
        $guzzleClient = \Mockery::mock(Client::class);

        $e = \Mockery::mock(ServerException::class);

        $guzzleClient->shouldReceive('request')->with(
            'get',
            $this->url . '?ip=' . $ip . '&key='
        )->andThrow($e);

        $this->server->send($guzzleClient, $ip);
    }
}
