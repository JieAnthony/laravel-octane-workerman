<?php

namespace JieAnthony\LaravelOctaneWorkerman\Workerman\Contracts;

use Workerman\Worker;

interface Bootstrap
{
    public static function start(Worker $worker);
}
