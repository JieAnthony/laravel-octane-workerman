<?php

defined('BASE_PATH') or define('BASE_PATH', $_SERVER['PWD'] ?? __DIR__.'/temp');

if (!function_exists('base_path')) {
    /**
     * @param $return_phar
     * @return false|string
     */
    function base_path($return_phar = true)
    {
        static $real_path = '';
        
        if (!$real_path) {
            $real_path = is_phar() ? dirname(Phar::running(false)) : BASE_PATH;
        }

        return $return_phar ? BASE_PATH : $real_path;
    }
}