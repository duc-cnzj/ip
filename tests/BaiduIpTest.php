<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\BaiduIp;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

class BaiduIpTest extends TestCase
{
    /**
     * @var BaiduIp
     */
    protected $server;

    protected $return = '{"address":"CN|\u6d59\u6c5f|\u7ecd\u5174|None|CMNET|0|0","content":{"address_detail":{"province":"\u6d59\u6c5f\u7701","city":"\u7ecd\u5174\u5e02","district":"","street":"","street_number":"","city_code":293},"address":"\u6d59\u6c5f\u7701\u7ecd\u5174\u5e02","point":{"y":"3482292.23","x":"13424438.13"}},"status":0}';

    protected $url = 'http://api.map.baidu.com/location/ip';

    protected function setUp(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $this->server = new BaiduIp();
    }

    /** @test */
    public function test_baidu_config()
    {
        $this->server->setConfig(['ak' => 'xxxxx']);

        $this->assertEquals('xxxxx', $this->server->getAk());
    }

    /** @test */
    public function test_baidu_send()
    {
        $ip = '127.0.0.1';

        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip . '&ak=')->andReturn($response);
        $response->shouldReceive('getBody')->andReturn($this->return);
        $this->assertEquals(
            $this->server->formatResult(['ip' => $ip], json_decode($this->return, true)),
            $this->server->send($guzzleClient, $ip)
        );
    }

    /** @test */
    public function test_send_with_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);
        $clientException = \Mockery::mock(ClientException::class);
        $serverException = \Mockery::mock(ServerException::class);
        $guzzleClient = \Mockery::mock(Client::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip . '&ak=')
            ->andThrowExceptions([$clientException, $serverException]);

        $this->server->send($guzzleClient, $ip);
    }
}
