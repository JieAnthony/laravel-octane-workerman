<?php

namespace JieAnthony\LaravelOctaneWorkerman\Process;

use Workerman\Timer;
use Workerman\Worker;
use Workerman\Protocols\Http;
use Workerman\Psr7\ServerRequest;
use Laravel\Octane\RequestContext;
use Laravel\Octane\ApplicationFactory;
use Workerman\Connection\TcpConnection;
use Laravel\Octane\Worker as OctaneWorker;
use Psr\Http\Message\ServerRequestInterface;
use Workerman\Connection\ConnectionInterface;
use JieAnthony\LaravelOctaneWorkerman\Workerman\WorkermanClient;

class HttpWorkerProcess
{
    protected $worker;

    public function onWorkerStart(Worker $worker)
    {
        $this->workerman = $worker;
        $this->workermanConfig = config('workerman');
        $this->httpConfig = config('workerman.http');
        $this->workermanClient = new WorkermanClient();

        Http::requestClass(ServerRequest::class);
        /** @var OctaneWorker $worker */
        $this->worker = tap((new OctaneWorker(
            new ApplicationFactory($_SERVER['APP_BASE_PATH']), $this->workermanClient
        )))->boot();
    }

    public function onWorkerStop(Worker $worker)
    {
        // var_dump("worker {$worker->id} stop");
    }

    public function onConnect(TcpConnection $connection)
    {
        $this->connection = $connection;

        // var_dump("client connect to worker_id {$this->workerman->id} successful, current connection_id is {$connection->id}");
    }

    public function onMessage(ConnectionInterface $connection, ServerRequest $psr7Request)
    {
        $worker = $this->worker;
        $workerman = $this->workerman;
        $workermanClient = $this->workermanClient;

        try {
            // bind webman request and response
            request_bind_connection($workerman, $worker, $connection, $psr7Request);
            response_bind_connection($workerman, $worker, $connection);
        } catch (\Throwable $e) {
            $connection->send($e->getMessage());

            exit(1);
        }

        if (!$psr7Request instanceof ServerRequestInterface) {
            return;
        }

        [$request, $context] = $workermanClient->marshalRequest(new RequestContext([
            'psr7Request' => $psr7Request,
            'connection' => $connection,
            'publicPath' => $this->httpConfig['publicPath'],
        ]));

        $worker->handle($request, $context);
    }

    public function onClose(TcpConnection $connection)
    {
        $connection->close();

        // var_dump("the worker_id {$this->workerman->id} of connection_id {$connection->id} closed");
    }
}
