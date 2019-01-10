<?php

namespace DucCnzj\Ip\Imp;

/**
 * Interface DataMapImp
 *
 * @package DucCnzj\Ip\Imp
 */
interface DataMapImp
{
    /**
     * @return bool
     *
     * @author duc <1025434218@qq.com>
     */
    public function hasInfo(): bool;

    /**
     * @param array $info
     *
     * @return DataMapImp
     *
     * @author duc <1025434218@qq.com>
     */
    public function setInfo(array $info): DataMapImp;

    /**
     * @return string|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getIp();

    /**
     * @return string|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCity();

    /**
     * @return string|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCountry();

    /**
     * @return string|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getRegion();

    /**
     * @return string|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getAddress();

    /**
     * @param string $name
     * @param        $params
     *
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function __call(string $name, $params);

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function __get(string $name);
}
