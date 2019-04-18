<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\DataMapper;
use DucCnzj\Ip\Imp\DataMapImp;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Exceptions\MethodNotExistException;

/**
 * Class MapperTest
 * @package DucCnzj\Ip\Tests
 */
class MapperTest extends TestCase
{
    /**
     * @var array
     */
    protected $data = [
        'ip'      => '127.0.0.1',
        'country' => '中国',
        'region'  => '浙江',
        'city'    => '绍兴',
        'address' => '中国浙江绍兴',
        'point_x' => '10.00',
        'point_y' => '20.00',
        'isp'     => '移动',
    ];

    /**
     * @var DataMapImp
     */
    protected $mapper;

    /**
     *
     * @author duc <1025434218@qq.com>
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mapper = new DataMapper();
    }

    /** @test */
    public function mapper_test()
    {
        $this->mapper->setInfo($this->data);

        $this->assertEquals('127.0.0.1', $this->mapper->getIp());

        $this->assertEquals('中国浙江绍兴', $this->mapper->getAddress());

        $this->assertEquals('绍兴', $this->mapper->getCity());

        $this->assertEquals('中国', $this->mapper->getCountry());

        $this->assertEquals('浙江', $this->mapper->getRegion());

        $this->assertEquals('移动', $this->mapper->getIsp());

        $this->assertEquals('10.00', $this->mapper->getPointX());
        $this->assertTrue($this->mapper->hasInfo());

        $this->assertEquals('', $this->mapper->getDuc());
        $this->assertEquals('', $this->mapper->duc);
    }

    /** @test */
    public function call_exception_test()
    {
        $this->expectException(MethodNotExistException::class);

        $this->mapper->setInfo($this->data);

        $this->mapper->duc();
    }
}
