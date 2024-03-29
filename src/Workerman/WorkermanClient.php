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
use Laravel\Octane\Contracts\ServesStaticFiles;
use Laravel\Octane\MimeType;
use Workerman\Protocols\Http\Response as WorkermanHttpResponse;
use Psr\Http\Message\ServerRequestInterface;
use JieAnthony\LaravelOctaneWorkerman\Workerman\Actions\ConvertWorkermanRequestToIlluminateRequest;

class WorkermanClient implements Client, StoppableClient, ServesStaticFiles
{
    use MarshalsPsr7RequestsAndResponses;

    /**
     * Marshal the given request context into an Illuminate request.
     *
     * @param  \Laravel\Octane\RequestContext  $context
     * @return array
     */
    public function marshalRequest(RequestContext $context): array
    {
        if ($context->request instanceof ServerRequestInterface) {
            $httpFoundationRequest = $this->toHttpFoundationRequest($context->request);
        } else {
            $httpFoundationRequest = (new ConvertWorkermanRequestToIlluminateRequest)(
                $context->request,
                PHP_SAPI
            );
        }

        return [
            $httpFoundationRequest,
            $context,
        ];
    }
    
    /**
     * Determine if the request can be served as a static file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Octane\RequestContext  $context
     * @return bool
     */
    public function canServeRequestAsStaticFile(Request $request, RequestContext $context): bool
    {
        if (! ($context->publicPath ?? false) ||
            $request->path() === '/') {
            return false;
        }

        $publicPath = $context->publicPath;

        $pathToFile = realpath($publicPath.'/'.$request->path());

        if ($this->isValidFileWithinSymlink($request, $publicPath, $pathToFile)) {
            $pathToFile = $publicPath.'/'.$request->path();
        }

        return $this->fileIsServable(
            $publicPath,
            $pathToFile,
        );
    }

    /**
     * Determine if the request is for a valid static file within a symlink.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $publicPath
     * @param  string  $pathToFile
     * @return bool
     */
    private function isValidFileWithinSymlink(Request $request, string $publicPath, string $pathToFile): bool
    {
        $pathAfterSymlink = $this->pathAfterSymlink($publicPath, $request->path());

        return $pathAfterSymlink && str_ends_with($pathToFile, $pathAfterSymlink);
    }

    /**
     * If the given public file is within a symlinked directory, return the path after the symlink.
     *
     * @param  string  $publicPath
     * @param  string  $path
     * @return string|bool
     */
    private function pathAfterSymlink(string $publicPath, string $path)
    {
        $directories = explode('/', $path);

        while ($directory = array_shift($directories)) {
            $publicPath .= '/'.$directory;

            if (is_link($publicPath)) {
                return implode('/', $directories);
            }
        }

        return false;
    }

    /**
     * Determine if the given file is servable.
     *
     * @param  string  $publicPath
     * @param  string  $pathToFile
     * @return bool
     */
    protected function fileIsServable(string $publicPath, string $pathToFile): bool
    {
        return $pathToFile &&
                ! in_array(pathinfo($pathToFile, PATHINFO_EXTENSION), ['php', 'htaccess', 'config']) &&
                str_starts_with($pathToFile, $publicPath) &&
                is_file($pathToFile);
    }

    /**
     * Serve the static file that was requested.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Octane\RequestContext  $context
     * @return void
     */
    public function serveStaticFile(Request $request, RequestContext $context): void
    {
        $publicPath = $context->publicPath;

        $pathToFile = realpath($publicPath.'/'.$request->path());

        $response = new WorkermanHttpResponse();
        $response->withStatus(200);
        $response->withHeader('Content-Type', MimeType::get(pathinfo($request->path(), PATHINFO_EXTENSION)));
        $response->withFile($pathToFile);

        $context->connection->send($response);
    }

    /**
     * Send the response to the server.
     *
     * @param  \Laravel\Octane\RequestContext  $context
     * @param  OctaneResponse  $response
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
     * @param  \Throwable  $e
     * @param  \Illuminate\Foundation\Application  $app
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Octane\RequestContext  $context
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
