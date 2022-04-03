<?php

namespace JieAnthony\LaravelOctaneWorkerman;

use Illuminate\Support\Arr;

trait WorkerTrait
{
    protected static $_bindInstance = [];

    protected static $_instance = null;

    public static function bindInstance(...$property)
    {
        foreach ($property as $prop) {
            static::setProp($prop);
        }
    }

    public static function getBindInstance($name = null)
    {
        if (str_contains($name, '.')) {
            $instance = Arr::get(static::$_bindInstance, $name);

            if ($instance) {
                return $instance;
            }
        }

        if ($name && array_key_exists($name, static::$_bindInstance)) {
            return static::$_bindInstance[$name];
        }

        return static::$_bindInstance;
    }

    protected static function setProp($prop)
    {
        $name = get_variable_name($prop);

        if ($name) {
            static::$_bindInstance[$name] = $prop;
        }
    }

    public function __call($method, $args)
    {
        foreach (static::$_bindInstance as $name => $instance) {
            if (method_exists($instance, $method)) {
                return $instance->$method(...$args);
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
