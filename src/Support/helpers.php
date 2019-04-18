<?php

if (! function_exists('toUnderScore')) {
    /**
     * @param $str
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    function toUnderScore($str)
    {
        $result = preg_replace_callback('/([A-Z]+)/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);

        return trim(preg_replace('/_{2,}/', '_', $result), '_');
    }
}
