<?php

namespace DucCnzj\Ip;

use DucCnzj\Ip\Imp\DataMapImp;
use DucCnzj\Ip\Exceptions\MethodNotExistException;

class NullDataMapper implements DataMapImp
{
    protected $info = [];

    public function getIp(): string
    {
        return '';
    }

    public function getCity(): string
    {
        return '';
    }

    public function getCountry(): string
    {
        return '';
    }

    public function getRegion(): string
    {
        return '';
    }

    public function getAddress(): string
    {
        return '';
    }

    /**
     * @param $name
     *
     * @return mixed|string
     *
     * @author duc <1025434218@qq.com>
     */
    public function __get($name)
    {
        $field = toUnderScore($name);

        return isset($this->info[$field]) ? $this->info[$field] : '';
    }

    /**
     * @param string $name
     * @param mixed $arguments
     *
     * @return mixed|string
     * @throws MethodNotExistException
     *
     * @author duc <1025434218@qq.com>
     */
    public function __call(string $name, $arguments)
    {
        preg_match('/get(.*)/', $name, $matches, PREG_OFFSET_CAPTURE);

        if (count($matches) >= 2 && strlen($name) > 3) {
            $field = toUnderScore($matches[1][0]);

            return isset($this->info[$field]) ? $this->info[$field] : '';
        }

        if (! method_exists($this, $name)) {
            throw new MethodNotExistException("{$name} 方法不存在");
        }
    }

    public function hasInfo(): bool
    {
        return false;
    }
}
