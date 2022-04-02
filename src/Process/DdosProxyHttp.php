<?php

namespace JieAnthony\LaravelOctaneWorkerman\Process;

use Workerman\Connection\AsyncTcpConnection;

class DdosProxyHttp
{
    public function onConnect($con)
    {
        $httpConfig = config('octane.gatewayworker.http');

        $rcon = new AsyncTcpConnection("tcp://{$httpConfig['host']}:{$httpConfig['port']}");

        $rcon->pipe($con);
        $con->pipe($rcon);

        $rcon->connect();
    }
}
