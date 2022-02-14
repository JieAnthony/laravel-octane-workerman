<?php

namespace JieAnthony\LaravelOctaneWorkerman\Workerman;

use DateTime;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use JieAnthony\LaravelOctaneWorkerman\Workerman\Actions\ConvertWorkermanRequestToIlluminateRequest;
use Laravel\Octane\Contracts\Client;
use Laravel\Octane\Contracts\ServesStaticFiles;
use Laravel\Octane\Contracts\StoppableClient;
use Laravel\Octane\MimeType;
use Laravel\Octane\Octane;
use Laravel\Octane\OctaneResponse;
use Laravel\Octane\RequestContext;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Workerman\Worker;
use Workerman\Protocols\Http\Response as WorkermanResponse;

class WorkermanClient implements Client, StoppableClient, ServesStaticFiles
{
    public const STATUS_CODE_REASONS = [
        419 => 'Page Expired',
    ];

    /**
     * Marshal the given request context into an Illuminate request.
     *
     * @param \Laravel\Octane\RequestContext $context
     * @return array
     */
    public function marshalRequest(RequestContext $context): array
    {
        return [
            (new ConvertWorkermanRequestToIlluminateRequest())(
                $context->connection,
                $context->workermanRequest,
                PHP_SAPI
            ),
            $context,
        ];
    }


    /**
     * Determine if the request can be served as a static file.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Laravel\Octane\RequestContext $context
     * @return bool
     */
    public function canServeRequestAsStaticFile(Request $request, RequestContext $context): bool
    {
        if (!($context->publicPath ?? false) ||
            $request->path() === '/') {
            return false;
        }

        $publicPath = $context->publicPath;

        $pathToFile = realpath($publicPath . '/' . $request->path());

        if ($this->isValidFileWithinSymlink($request, $publicPath, $pathToFile)) {
            $pathToFile = $publicPath . '/' . $request->path();
        }

        return $this->fileIsServable(
            $publicPath,
            $pathToFile,
        );
    }

    /**
     * Serve the static file that was requested.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Laravel\Octane\RequestContext $context
     * @return void
     */
    public function serveStaticFile(Request $request, RequestContext $context): void
    {
        $publicPath = $context->publicPath;

        $headers = [
            'Content-Type' => MimeType::get(pathinfo($request->path(), PATHINFO_EXTENSION))
        ];

        $context->connection->send((new WorkermanResponse(200, $headers))->withFile(realpath($publicPath . '/' . $request->path())));
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
        $response = $octaneResponse->response;

        $workermanResponse = new WorkermanResponse();

        if (!$response->headers->has('Date')) {
            $response->setDate(DateTime::createFromFormat('U', time()));
        }

        $headers = $response->headers->allPreserveCase();

        if (isset($headers['Set-Cookie'])) {
            unset($headers['Set-Cookie']);
        }

        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $workermanResponse->header($name, $value);
            }
        }

        if (!is_null($reason = $this->getReasonFromStatusCode($response->getStatusCode()))) {
            $workermanResponse->withStatus($response->getStatusCode(), $reason);
        } else {
            $workermanResponse->withStatus($response->getStatusCode());
        }

        foreach ($response->headers->getCookies() as $cookie) {
            $workermanResponse->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getMaxAge(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                $cookie->getSameSite()
            );
        }

        if ($response instanceof BinaryFileResponse) {
            $workermanResponse->withFile($response->getFile()->getPathname());
        }

        if ($octaneResponse->outputBuffer) {
            $workermanResponse->withBody($octaneResponse->outputBuffer);
        }

        if ($response instanceof StreamedResponse) {
            ob_start(function ($data) use ($workermanResponse) {
                if (strlen($data) > 0) {
                    $workermanResponse->withBody($data);
                }

                return '';
            }, 1);

            $response->sendContent();

            ob_end_clean();

            $context->connection->send($workermanResponse);

            return;
        }

        $content = $response->getContent();

        $workermanResponse->withProtocolVersion($response->getProtocolVersion());
        $workermanResponse->withBody($content);

        $context->connection->send($workermanResponse);
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
        $workermanResponse = new WorkermanResponse();
        $workermanResponse->withStatus(500);
        $workermanResponse->header('Status', '500 Internal Server Error');
        $workermanResponse->header('Content-Type', 'text/plain');
        $workermanResponse->withBody(Octane::formatExceptionForClient($e, $app->make('config')->get('app.debug')));

        $context->connection->send($workermanResponse);
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

    /**
     * Determine if the given file is servable.
     *
     * @param string $publicPath
     * @param string $pathToFile
     * @return bool
     */
    protected function fileIsServable(string $publicPath, string $pathToFile): bool
    {
        return $pathToFile &&
            !in_array(pathinfo($pathToFile, PATHINFO_EXTENSION), ['php', 'htaccess', 'config']) &&
            str_starts_with($pathToFile, $publicPath) &&
            is_file($pathToFile);
    }

    /**
     * Determine if the request is for a valid static file within a symlink.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $publicPath
     * @param string $pathToFile
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
     * @param string $publicPath
     * @param string $path
     * @return string|bool
     */
    private function pathAfterSymlink(string $publicPath, string $path)
    {
        $directories = explode('/', $path);

        while ($directory = array_shift($directories)) {
            $publicPath .= '/' . $directory;

            if (is_link($publicPath)) {
                return implode('/', $directories);
            }
        }

        return false;
    }


    /**
     * Get the HTTP reason clause for non-standard status codes.
     *
     * @param int $code
     * @return string|null
     */
    protected function getReasonFromStatusCode(int $code): ?string
    {
        if (array_key_exists($code, self::STATUS_CODE_REASONS)) {
            return self::STATUS_CODE_REASONS[$code];
        }

        return null;
    }
}
