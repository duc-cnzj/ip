<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\Exceptions\ServerErrorException;
use GuzzleHttp\Client;
use DucCnzj\Ip\RequestHandler;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Strategies\TaobaoIp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\RequestInterface;

class RequestHandlerTest extends TestCase
{
    protected $ip = '127.0.0.1';
    /**
     * @var RequestHandlerImp
     */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();
        $this->handler = new RequestHandler();
    }

    /** @test */
    public function get_handler_test()
    {
        $handler = new RequestHandler();

        $this->assertInstanceOf(Client::class, $handler->getClient());

        $this->assertSame($handler->getClient(), $handler->getClient());
    }

    /** @test */
    public function test_try_times()
    {
        $handler = new RequestHandler();

        $handler->setTryTimes(10);

        $this->assertEquals(10, $handler->getTryTimes());
    }

    /** @test */
    public function break_loop_test()
    {
        $this->expectException(ServerErrorException::class);

        $request = \Mockery::mock(RequestInterface::class);
        $taobao = \Mockery::mock(TaobaoIp::class);
        $connectExecption = new ConnectException('网络连接失败', $request);
        $taobao->shouldReceive('send')->andThrow($connectExecption);
        $providers = ['taobao' => $taobao];

        $this->handler->send($providers, $this->ip);
        $this->assertEquals(['网络连接失败'], $this->handler->getErrors());
    }
}
