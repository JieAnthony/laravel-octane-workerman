<?php

namespace JieAnthony\LaravelOctaneWorkerman;

trait WorkerTrait
{
    protected static $workermanWorker = null;

    protected static $laravelOctaneWorker = null;

    protected static $workermanConnectionTcpConnection = null;

    protected static $workermanProtocolsHttpRequest = null;

    protected static $properties = [];

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
            static::$properties[] = $name;
        }
    }

    public function __call($method, $args)
    {
        foreach (static::$properties as $property) {
            if (static::$$property && method_exists(static::$$property, $method)) {
                return static::$$property->$method(...$args);
            }
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
