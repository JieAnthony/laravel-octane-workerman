<?php

namespace JieAnthony\LaravelOctaneWorkerman;

class Gateway
{
    public static function resolveRegisterAddress(?string $registerAddress = null)
    {
        if (!$registerAddress) {
            $registerConfig = config('octane.gatewayworker.register');
            $registerAddress = sprintf("%s:%s", $registerConfig['host'], $registerConfig['port']);
        }

        \GatewayClient\Gateway::$registerAddress = $registerAddress;
        \GatewayWorker\Gateway::$registerAddress = $registerAddress;
        \GatewayWorker\Lib\Gateway::$registerAddress = $registerAddress;
    }
}