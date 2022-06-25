<?php

use Psr\Http\Message\ServerRequestInterface;
use Laravel\Octane\ApplicationFactory;
use Laravel\Octane\RequestContext;
use Laravel\Octane\Worker;
use Workerman\Protocols\Http;
use Workerman\Psr7\ServerRequest;
use Workerman\Worker as WorkermanWorker;
use Workerman\Connection\ConnectionInterface;
use JieAnthony\LaravelOctaneWorkerman\Workerman\WorkermanClient;

if (!$httpConfig['enable']) {
    return;
}

$workerman = new WorkermanWorker($httpConfig['listen'], $httpConfig['context'] ?? []);

$workerman->count = $httpConfig['count'] ?: cpu_count() * 2;
$workerman->name = $httpConfig['name'];
$workerman->user = $httpConfig['user'];
$workerman->group = $httpConfig['group'];
$workerman->reusePort = $httpConfig['reusePort'];
$workerman->transport = $httpConfig['transport'];

$workermanClient = new WorkermanClient;

/** @var Worker $worker */
$worker = tap((new Worker(
    new ApplicationFactory($basePath), $workermanClient
)))->boot();

Http::requestClass(ServerRequest::class);
$workerman->onMessage = function(ConnectionInterface $connection, ServerRequest $psr7Request) use(
    &$worker,
    $workermanClient,
    $workerman,
) {
    try {
        // bind webman request and response
        request_bind_connection($workerman, $worker, $connection, $psr7Request);
        response_bind_connection($workerman, $worker, $connection);
    } catch (Throwable $e) {
        $connection->send($e->getMessage());

        exit(1);
    }

    if (! $psr7Request instanceof ServerRequestInterface) {
        return;
    }

    [$request, $context] = $workermanClient->marshalRequest(new RequestContext([
        'psr7Request' => $psr7Request,
        'connection' => $connection,
    ]));

    $worker->handle($request, $context);
};
