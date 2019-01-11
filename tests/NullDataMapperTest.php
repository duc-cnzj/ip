<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\DataMapper;
use DucCnzj\Ip\NullDataMapper;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Exceptions\MethodNotExistException;

class NullDapperTest extends TestCase
{
    /**
     * @var NullDataMapper
     */
    protected $mapper;

    protected $ip = '127.0.0.1';

    protected function setUp()
    {
        parent::setUp();

        $this->mapper = new NullDataMapper();
    }

    /** @test */
    public function null_object_mapper_test()
    {
        $this->assertNull($this->mapper->getAddress());
        $this->assertNull($this->mapper->getCity());
        $this->assertNull($this->mapper->getCountry());
        $this->assertNull($this->mapper->getRegion());
        $this->assertNull($this->mapper->getIp());
        $this->assertFalse($this->mapper->hasInfo());

        $this->mapper->setInfo(['ip' => $this->ip]);
        $this->assertEquals($this->ip, $this->mapper->getIp());

    }

    /** @test */
    function null_object_get_test()
    {
        $this->assertNull($this->mapper->duc);
        $this->assertNull($this->mapper->getDuc());
    }

    /** @test */
    public function call_exception_test()
    {
        $this->expectException(MethodNotExistException::class);

        $this->mapper->duc();
    }
}
