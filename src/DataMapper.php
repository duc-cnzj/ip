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
     * DataMapper constructor.
     *
     * @param array $info
     */
    public function __construct(array $info)
    {
        $this->info = $info;
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCity(): string
    {
        return $this->info['city'];
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCountry(): string
    {
        return $this->info['country'];
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getRegion(): string
    {
        return $this->info['region'];
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getAddress(): string
    {
        return $this->info['address'];
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
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getIp(): string
    {
        return $this->info['ip'];
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
}
