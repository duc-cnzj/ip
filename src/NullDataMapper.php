<?php

namespace DucCnzj\Ip;

use DucCnzj\Ip\Imp\DataMapImp;
use DucCnzj\Ip\Exceptions\MethodNotExistException;

/**
 *
 * Class NullDataMapper
 *
 * @package DucCnzj\Ip
 */
class NullDataMapper implements DataMapImp
{
    /**
     * @var array
     */
    protected $info = [];

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
    public function getIp(): string
    {
        return $this->info['ip'];
    }

    /**
     * @return null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCity()
    {
        return null;
    }

    /**
     * @return null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCountry()
    {
        return null;
    }

    /**
     * @return null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getRegion()
    {
        return null;
    }

    /**
     * @return null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getAddress()
    {
        return null;
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

        return isset($this->info[$field]) ? $this->info[$field] : null;
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

            return isset($this->info[$field]) ? $this->info[$field] : null;
        }

        if (! method_exists($this, $name)) {
            throw new MethodNotExistException("{$name} 方法不存在");
        }
    }

    /**
     * @return bool
     *
     * @author duc <1025434218@qq.com>
     */
    public function hasInfo(): bool
    {
        return false;
    }
}
