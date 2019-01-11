<?php

namespace DucCnzj\Ip;

use DucCnzj\Ip\Imp\DataMapImp;
use DucCnzj\Ip\Exceptions\MethodNotExistException;

/**
 *
 * Class DataMapper
 *
 * @package DucCnzj\Ip
 */
class DataMapper implements DataMapImp
{
    /**
     * @var null|array
     */
    protected $info = null;

    /**
     * @param array $info
     *
     * @return DataMapImp
     *
     * @author duc <1025434218@qq.com>
     */
    public function setInfo(array $info): DataMapImp
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCity(): string
    {
        return $this->getField('city');
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCountry(): string
    {
        return $this->getField('country');
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getRegion(): string
    {
        return $this->getField('region');
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getAddress(): string
    {
        return $this->getField('address');
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getIp(): string
    {
        return $this->getField('ip');
    }

    /**
     * @return bool
     *
     * @author duc <1025434218@qq.com>
     */
    public function hasInfo(): bool
    {
        return ! is_null($this->info);
    }

    /**
     * @param $name
     *
     * @return mixed|string
     *
     * @author duc <1025434218@qq.com>
     */
    public function __get(string $name)
    {
        $field = toUnderScore($name);

        return $this->getField($field);
    }

    /**
     * @param string $name
     * @param        $arguments
     *
     * @return string
     * @throws MethodNotExistException
     *
     * @author duc <1025434218@qq.com>
     */
    public function __call(string $name, $arguments)
    {
        preg_match('/get(.*)/', $name, $matches, PREG_OFFSET_CAPTURE);

        if (count($matches) >= 2 && strlen($name) > 3) {
            $field = toUnderScore($matches[1][0]);

            return $this->getField($field);
        }

        throw new MethodNotExistException("{$name} 方法不存在");
    }

    /**
     * @param string $field
     *
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getField(string $field):string
    {
        return isset($this->info[$field]) ? $this->info[$field] : '';
    }
}
