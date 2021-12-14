<?php

namespace JieAnthony\LaravelOctaneWorkerman\Workerman;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Octane\Contracts\Client;
use Laravel\Octane\Contracts\StoppableClient;
use Laravel\Octane\MarshalsPsr7RequestsAndResponses;
use Laravel\Octane\Octane;
use Laravel\Octane\OctaneResponse;
use Laravel\Octane\RequestContext;
use Throwable;
use Workerman\Worker;
use Workerman\Psr7\Response as WorkermanResponse;

class WorkermanClient implements Client, StoppableClient
{
    use MarshalsPsr7RequestsAndResponses;

    /**
     * Marshal the given request context into an Illuminate request.
     *
     * @param \Laravel\Octane\RequestContext $context
     * @return array
     */
    public function marshalRequest(RequestContext $context): array
    {
        return [
            $this->toHttpFoundationRequest($context->psr7Request),
            $context,
        ];
    }

    /**
     * Send the response to the server.
     *
     * @param \Laravel\Octane\RequestContext $context
     * @param OctaneResponse $response
     * @return void
     */
    public function respond(RequestContext $context, OctaneResponse $octaneResponse): void
    {
        $response = $this->toPsr7Response($octaneResponse->response);

        $context->connection->send(new WorkermanResponse(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        ));
    }

    /**
     * Send an error message to the server.
     *
     * @param \Throwable $e
     * @param \Illuminate\Foundation\Application $app
     * @param \Illuminate\Http\Request $request
     * @param \Laravel\Octane\RequestContext $context
     * @return void
     */
    public function error(Throwable $e, Application $app, Request $request, RequestContext $context): void
    {
        $context->connection->send(Octane::formatExceptionForClient(
            $e,
            $app->make('config')->get('app.debug')
        ));
    }

    /**
     * Stop the underlying server / worker.
     *
     * @return void
     */
    public function stop(): void
    {
        Worker::stopAll();
    }
}