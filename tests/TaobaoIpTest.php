<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\TaobaoIp;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

class TaobaoIpTest extends TestCase
{
    /**
     * @var TaobaoIp
     */
    protected $server;

    protected $return = '{"code":0,"data":{"ip":"117.149.174.132","country":"中国","area":"","region":"浙江","city":"绍兴","county":"XX","isp":"移动","country_id":"CN","area_id":"","region_id":"330000","city_id":"330600","county_id":"xx","isp_id":"100025"}}';

    protected $url = 'http://ip.taobao.com/service/getIpInfo.php';

    protected function setUp(
    )/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $this->server = new TaobaoIp();
    }

    /** @test */
    public function test_taobao_send()
    {
        $ip = '127.0.0.1';

        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip)->andReturn($response);
        $response->shouldReceive('getBody')->andReturn($this->return);

        $this->assertEquals($this->server->formatResult(['ip' => $ip], json_decode($this->return, true)), $this->server->send($guzzleClient, $ip));
    }

    /** @test */
    public function test_send_with_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);
        $clientException = \Mockery::mock(ClientException::class);
        $serverException = \Mockery::mock(ServerException::class);
        $guzzleClient = \Mockery::mock(Client::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip)
            ->andThrowExceptions([$clientException, $serverException]);

        $this->server->send($guzzleClient, $ip);
    }

    /** @test */
    public function test_server_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);

        $serverException = \Mockery::mock(ServerException::class);

        $guzzleClient = \Mockery::mock(Client::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip)
            ->andThrow($serverException);

        $this->server->send($guzzleClient, $ip);
    }

    /** @test */
    public function test_server_error_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);

        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip)
            ->andReturn($response);
        $response->shouldReceive('getBody')->andReturn(json_encode(['code' => 1]));

        $this->server->send($guzzleClient, $ip);
    }
}
