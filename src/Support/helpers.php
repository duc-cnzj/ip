<?php

if (! function_exists('toUnderScore')) {
    function toUnderScore($str)
    {
        $result = preg_replace_callback('/([A-Z]+)/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);

        return trim(preg_replace('/_{2,}/', '_', $result), '_');
    }
}
