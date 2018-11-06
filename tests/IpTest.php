<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use DucCnzj\Ip\IpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Exceptions\InvalidIpAddress;

class IpTest extends TestCase
{
    /** @test */
    public function testGetInfoWithInvalidIp()
    {
        $ip = new IpClient;
        $this->expectException(InvalidIpAddress::class);
        $this->expectExceptionMessage('ip 地址格式不正确');
        $ip->getIpInfo();
        $this->fail('ip 地址正确');
    }

    /** @test */
    public function testGetInfoByIp()
    {
        $response = new Response(200, [], '"{"code":0}"');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('http://ip.taobao.com/service/getIpInfo.php', [
            'query' => ['ip' => '123.456.789.111'],
        ])->andReturn($response);

        $ip = \Mockery::mock(IpClient::class, ['123.456.789.111'])->makePartial();
        $ip->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(
            '"{"code":0}"',
            $ip->getIpInfo()
        );
    }

    /** @test */
    public function testSetGuzzleOptions()
    {
        $c = new IpClient;

        // 设置参数前，timeout 为 null
        $this->assertNull($c->getHttpClient()->getConfig('timeout'));

        // 设置参数
        $c->setGuzzleOptions(['timeout' => 5000]);

        // 设置参数后，timeout 为 5000
        $this->assertSame(5000, $c->getHttpClient()->getConfig('timeout'));
    }
}
