<?php

namespace JieAnthony\LaravelOctaneWorkerman;

class WebmanResponse
{
    protected static $workerman = null;

    protected static $worker = null;

    protected static $connection = null;

    public static function bindConnection($workerman = null, $worker = null, $connection = null)
    {
        static::$workerman = $workerman ?? static::$workerman;
        static::$worker = $worker ?? static::$worker;
        static::$connection = $connection ?? static::$connection;
    }
    
    public function __call($method, $args)
    {
        if (method_exists(\response(), $method)) {
            return \response()->$method(...$args);
        }

        if (method_exists(static::$connection, $method)) {
            return static::$connection->$method(...$args);
        }

        if (method_exists(static::$worker, $method)) {
            return static::$connection->$method(...$args);
        }

        throw new \Exception("\\response() not found method {$method}, please contact my24251325@gmail.com");
    }
}