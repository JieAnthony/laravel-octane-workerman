<?php

use GatewayWorker\Register;
use GatewayWorker\Gateway;
use GatewayWorker\BusinessWorker;

// register 服务必须是text协议
if ($workermanConfig['register']['enable']) {
    $register = new Register("text://{$workermanConfig['register']['host']}:{$workermanConfig['register']['port']}");

    $register->name = $workermanConfig['register']['name'];
    $register->onWorkerStart = function ($worker) {
    };
}

// gateway 进程, websocket 协议
if ($workermanConfig['gateway-websocket']['enable']) {
    $gatewayWebsocket = new Gateway("{$workermanConfig['gateway-websocket']['protocol']}://{$workermanConfig['gateway-websocket']['host']}:{$workermanConfig['gateway-websocket']['port']}");

    $gatewayWebsocket->name = $workermanConfig['gateway-websocket']['name'];
    $gatewayWebsocket->count = $workermanConfig['gateway-websocket']['count'];
    $gatewayWebsocket->lanIp = $workermanConfig['gateway-websocket']['lanIp'];
    $gatewayWebsocket->startPort = $workermanConfig['gateway-websocket']['startPort'];
    $gatewayWebsocket->pingInterval = $workermanConfig['gateway-websocket']['pingInterval'];
    $gatewayWebsocket->pingData = $workermanConfig['gateway-websocket']['pingData'];
    $gatewayWebsocket->registerAddress = $workermanConfig['gateway-websocket']['registerAddress'];
    $gatewayWebsocket->onWorkerStart = $workermanConfig['gateway-websocket']['onWorkerStart'] ?? function ($worker) {
    };
    $gatewayWebsocket->onWorkerStop = $workermanConfig['gateway']['onWorkerStop'] ?? function () {
    };
    $gatewayWebsocket->onConnect = $workermanConfig['gateway']['onConnect'] ?? function () {
    };
    $gatewayWebsocket->onClose = $workermanConfig['gateway']['onClose'] ?? function () {
    };
}

// gateway 进程, tcp 协议
if ($workermanConfig['gateway-tcp']['enable']) {
    $gatewayTcp = new Gateway("{$workermanConfig['gateway-tcp']['protocol']}://{$workermanConfig['gateway-tcp']['host']}:{$workermanConfig['gateway-tcp']['port']}");

    $gatewayTcp->name = $workermanConfig['gateway-tcp']['name'];
    $gatewayTcp->count = $workermanConfig['gateway-tcp']['count'];
    $gatewayTcp->lanIp = $workermanConfig['gateway-tcp']['lanIp'];
    $gatewayTcp->startPort = $workermanConfig['gateway-tcp']['startPort'];
    $gatewayTcp->pingInterval = $workermanConfig['gateway-tcp']['pingInterval'];
    $gatewayTcp->pingData = $workermanConfig['gateway-tcp']['pingData'];
    $gatewayTcp->registerAddress = $workermanConfig['gateway-tcp']['registerAddress'];
    $gatewayTcp->onWorkerStart = $workermanConfig['gateway-tcp']['onWorkerStart'] ?? function ($worker) {
    };
    $gatewayTcp->onWorkerStop = $workermanConfig['gateway']['onWorkerStop'] ?? function () {
    };
    $gatewayTcp->onConnect = $workermanConfig['gateway']['onConnect'] ?? function () {
    };
    $gatewayTcp->onClose = $workermanConfig['gateway']['onClose'] ?? function () {
    };
}

// bussinessWorker 进程
if ($workermanConfig['business']['enable']) {
    $business = new BusinessWorker("{$workermanConfig['business']['protocol']}://{$workermanConfig['business']['host']}:{$workermanConfig['business']['port']}");

    $business->name = $workermanConfig['business']['name'];
    $business->count = $workermanConfig['business']['count'];
    $business->registerAddress = $workermanConfig['business']['registerAddress'];
    $business->onWorkerStart = $workermanConfig['business']['onWorkerStart'] ?? function (BusinessWorker $worker) {
    };
    $business->onWorkerStop = $workermanConfig['business']['onWorkerStop'] ?? function (BusinessWorker $businessWorker) {
    };

    if (empty($workermanConfig['business']['eventHandler']) && !class_exists('Events')) {
        if (!class_exists('Events')) {
            class Events {
                public static function onMessage(string $client_id, string $recv_data)
                {
                    var_dump("client_id $client_id recv_data $recv_data");
                }
            };
        }
    }

    $business->eventHandler = $workermanConfig['business']['eventHandler'] ?? 'Events';
}
