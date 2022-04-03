<?php

namespace JieAnthony\LaravelOctaneWorkerman;

trait WorkerTrait
{
    protected static $workermanWorker = null;

    protected static $laravelOctaneWorker = null;

    protected static $workermanConnectionTcpConnection = null;

    protected static $workermanProtocolsHttpRequest = null;

    protected static $_instance = null;

    public static function bindConnection(...$property)
    {
        foreach ($property as $prop) {
            static::setProp($prop);
        }
    }

    protected static function setProp($prop)
    {
        $name = get_variable_name($prop);

        if ($name) {
            static::$$name = $prop;
        }
    }
    
    public function __call($method, $args)
    {
        if (method_exists(\request(), $method)) {
            return \request()->$method(...$args);
        }

        if (method_exists(static::$connection, $method)) {
            return static::$connection->$method(...$args);
        }

        if (method_exists(static::$worker, $method)) {
            return static::$connection->$method(...$args);
        }

        throw new \Exception("\\request() not found method {$method}, please contact my24251325@gmail.com");
    }

    public static function __callStatic($method, $args)
    {
        if (!static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance->$method(...$args);
    }
}