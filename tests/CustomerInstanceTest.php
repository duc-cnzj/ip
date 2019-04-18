<?php

namespace DucCnzj\Ip\Tests;

use RuntimeException;
use DucCnzj\Ip\IpClient;
use DucCnzj\Ip\Imp\IpImp;
use DucCnzj\Ip\NullDataMapper;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Imp\RequestHandlerImp;

/**
 * Class IpTest
 * @package DucCnzj\Ip\Tests
 */
class CustomerInstanceTest extends TestCase
{
    /**
     * @var IpClient
     */
    protected $client;

    /**
     *
     * @author duc <1025434218@qq.com>
     */
    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $this->client = new IpClient();
    }

    /** @test */
    public function user_can_custom_instance()
    {
        $ip = '127.0.0.1';
        $obj = \Mockery::mock(IpImp::class);
        $obj->shouldReceive('send')->andReturn([]);
        $res = $this->client->bind('customer', $obj, false)
            ->use('customer')
            ->setIp($ip)
            ->getOriginalInfo();
        $this->assertEquals([
            'provider' => 'customer',
            'success'  => 1,
        ], $res);

        $obj->shouldHaveReceived('send')->times(1);
    }

    /** @test */
    public function instance_must_instanceof_imp_with_obj()
    {
        $this->expectException(RuntimeException::class);
        $ip = '127.0.0.1';
        $obj = \Mockery::mock(RequestHandlerImp::class);

        $this->client->bind('customer', $obj, false)
            ->use('customer')
            ->setIp($ip)
            ->getOriginalInfo();
    }

    /** @test */
    public function instance_must_instanceof_imp_with_string()
    {
        $this->expectException(RuntimeException::class);
        $ip = '127.0.0.1';
        $this->client->bind('customer', NullDataMapper::class, false)
            ->use('customer')
            ->setIp($ip)
            ->getOriginalInfo();
    }

    /** @test */
    public function instance_must_instanceof_imp_with_string_error()
    {
        $this->expectException(RuntimeException::class);
        $ip = '127.0.0.1';
        $this->client->bind('customer', 'ClassNotExist', false)
            ->use('customer')
            ->setIp($ip)
            ->getOriginalInfo();
    }
}
