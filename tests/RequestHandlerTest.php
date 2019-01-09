<?php

namespace DucCnzj\Ip\Tests;

use GuzzleHttp\Client;
use DucCnzj\Ip\RequestHandler;
use PHPUnit\Framework\TestCase;

class RequestHandlerTest extends TestCase
{
    /** @test */
    public function get_handler_test()
    {
        $handler = new RequestHandler();

        $this->assertInstanceOf(Client::class, $handler->getClient());

        $this->assertSame($handler->getClient(), $handler->getClient());
    }

    /** @test */
    function test_try_times()
    {
        $handler = new RequestHandler();

        $handler->setTryTimes(10);

        $this->assertEquals(10, $handler->tryTimes);

    }
}
