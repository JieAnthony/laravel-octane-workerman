<?php

namespace JieAnthony\LaravelOctaneWorkerman;

class WebmanResponse
{
    use WorkerTrait {
        __call as workerCall;
    }

    public function __call($method, $args)
    {
        if (method_exists(\response(), $method)) {
            return \response()->$method(...$args);
        }

        return static::worekrCall($method, $args);
    }
}