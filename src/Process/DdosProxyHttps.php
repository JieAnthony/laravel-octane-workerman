<?php

require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;

$worker = new Worker('tcp://0.0.0.0:80');
$worker->onConnect = function ($con) {
};

$worker = new Worker('tcp://0.0.0.0:443');
$worker->count = 8;
$worker->onConnect = function ($con) {
   $rcon = new AsyncTcpConnection('tcp://内网ip:443');
   $rcon->pipe($con);
   $con->pipe($rcon);
   $rcon->connect();
};


class DdosProxyHttps
{
    public function onConnect($con)
    {
        $rcon = new AsyncTcpConnection('tcp://内网ip:80');
        $rcon->pipe($con);
        $con->pipe($rcon);
        $rcon->connect();
    }
}
