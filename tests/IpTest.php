<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\IpClient;
use DucCnzj\Ip\Imp\IpImp;
use DucCnzj\Ip\DataMapper;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\TaobaoIp;
use DucCnzj\Ip\Exceptions\IpProviderClassNotExistException;

class IpTest extends TestCase
{
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

//    /** @test */
//    function test_get_ip()
//    {
//        $client = new IpClient();
//        $client->setProviderConfig('baidu', ['ak' => 'swXuvzN8SoZeQUwcV1mtcMQhjAEMDyq5']);
//        $client->setIp('126.0.0.1');
//        var_dump($client->getCountry());
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
}
