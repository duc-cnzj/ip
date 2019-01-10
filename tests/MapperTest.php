<?php

namespace DucCnzj\Ip\Tests;

use DucCnzj\Ip\DataMapper;
use PHPUnit\Framework\TestCase;
use DucCnzj\Ip\Exceptions\MethodNotExistException;

class MapperTest extends TestCase
{
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

    /** @test */
    public function mapper_test()
    {
        $map = new DataMapper($this->data);

        $this->assertEquals('127.0.0.1', $map->getIp());

        $this->assertEquals('中国浙江绍兴', $map->getAddress());

        $this->assertEquals('绍兴', $map->getCity());

        $this->assertEquals('中国', $map->getCountry());

        $this->assertEquals('浙江', $map->getRegion());

        $this->assertEquals('移动', $map->getIsp());

        $this->assertEquals('10.00', $map->getPointX());

        $this->assertEquals('', $map->getDuc());
        $this->assertEquals('', $map->duc);
    }

    /** @test */
    public function call_exception_test()
    {
        $this->expectException(MethodNotExistException::class);

        $map = new DataMapper($this->data);

        $map->duc();
    }
}
