<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\AliIp;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\AnalysisException;
use DucCnzj\Ip\Exceptions\BreakLoopException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

class AliIpTest extends TestCase
{
    protected $url = 'http://iploc.market.alicloudapi.com/v3/ip';

    protected $successReturn = '{"status":"1","info":"OK","infocode":"10000","province":"北京市","city":"北京市","adcode":"110000","rectangle":"116.0119343,39.66127144;116.7829835,40.2164962"}';

    protected $failReturn = '{"status":"1","info":"OK","infocode":"10000","province":[],"city":[],"adcode":[],"rectangle":[]}';

    /**
     * @var AliIp
     */
    protected $server;

    protected function setUp()
    {
        parent::setUp();
        $this->server = new AliIp();
    }

    /** @test */
    public function test_ali_config()
    {
        $this->server->setConfig(['app_code' => 'xxxxx']);

        $this->assertEquals('xxxxx', $this->server->getAppCode());

        $this->server->setConfig('');

        $this->assertEquals('', $this->server->getAppCode());
    }

    /** @test */
    public function test_ali_fail_send()
    {
        $ip = '127.0.0.1';

        $this->expectException(AnalysisException::class);

        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip, [
            'headers' => [
                'Authorization' => 'APPCODE ',
            ],
        ])->andReturn($response);

        $response->shouldReceive('getBody')->andReturn($this->failReturn);
        $this->server->send($guzzleClient, $ip);
    }

    /** @test */
    public function test_ali_success_send()
    {
        $ip = '127.0.0.1';

        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip, [
            'headers' => [
                'Authorization' => 'APPCODE xxx',
            ],
        ])->andReturn($response);

        $response->shouldReceive('getBody')->andReturn($this->successReturn);

        $this->server->setConfig(['app_code' => 'xxx']);

        $this->assertEquals(
            $this->server->formatResult(['ip' => $ip], json_decode($this->successReturn, true)),
            $this->server->send($guzzleClient, $ip)
        );
    }

    /** @test */
    public function test_client_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(BreakLoopException::class);
        $guzzleClient = \Mockery::mock(Client::class);
        $response = \Mockery::mock(ResponseInterface::class);

        $e = \Mockery::mock(ClientException::class);
        $e->shouldReceive('getResponse')->andReturn($response);

        $response->shouldReceive('getStatusCode')->andReturn(400);

        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip, [
            'headers' => [
                'Authorization' => 'APPCODE ',
            ],
        ])->andThrow($e);

        $this->server->send($guzzleClient, $ip);
    }

    /** @test */
    public function test_server_exception()
    {
        $ip = '127.0.0.1';

        $this->expectException(ServerErrorException::class);
        $guzzleClient = \Mockery::mock(Client::class);

        $e = \Mockery::mock(ServerException::class);

        $guzzleClient->shouldReceive('request')->with('get', $this->url . '?ip=' . $ip, [
            'headers' => [
                'Authorization' => 'APPCODE ',
            ],
        ])->andThrow($e);

        $this->server->send($guzzleClient, $ip);
    }
}
