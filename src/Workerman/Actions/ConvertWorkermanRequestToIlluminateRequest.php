<?php

namespace JieAnthony\LaravelOctaneWorkerman\Workerman\Actions;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class ConvertWorkermanRequestToIlluminateRequest
{
    /**
     * Convert the given Workerman request into an Illuminate request.
     *
     * @param \Workerman\Connection\TcpConnection $connection
     * @param \Workerman\Protocols\Http\Request $workermanRequest
     * @param string $phpSapi
     * @return \Illuminate\Http\Request
     */
    public function __invoke($connection, $workermanRequest, string $phpSapi): Request
    {
        $customizeServer = [
            'request_method' => $workermanRequest->method(),
            'request_uri' => $workermanRequest->path(),
            'path_info' => $workermanRequest->path(),
            'request_time' => $_SERVER['REQUEST_TIME'],
            'request_time_float' => $_SERVER['REQUEST_TIME_FLOAT'],
            'server_protocol' => 'HTTP/' . $workermanRequest->protocolVersion(),
            'server_port' => $connection->getLocalPort(),
            'remote_port' => $connection->getRemotePort(),
            'remote_addr' => $connection->getRemoteIp(),
        ];
        if ($queryString = $workermanRequest->queryString()) {
            $customizeServer['query_string'] = $queryString;
        }

        $serverVariables = $this->prepareServerVariables(
            $customizeServer,
            $workermanRequest->header() ?? [],
            $phpSapi
        );

        $request = new SymfonyRequest(
            $workermanRequest->get() ?? [],
            $workermanRequest->post() ?? [],
            [],
            $workermanRequest->cookie() ?? [],
            $workermanRequest->file() ?? [],
            $serverVariables,
            $workermanRequest->rawBody(),
        );

        if (str_starts_with((string)$request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded') &&
            in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'PATCH', 'DELETE'])) {
            parse_str($request->getContent(), $data);

            $request->request = new ParameterBag($data);
        }

        return Request::createFromBase($request);
    }

    /**
     * Parse the "server" variables and headers into a single array of $_SERVER variables.
     *
     * @param array $server
     * @param array $headers
     * @param string $phpSapi
     * @return array
     */
    protected function prepareServerVariables(array $server, array $headers, string $phpSapi): array
    {
        $results = [];

        foreach ($server as $key => $value) {
            $results[strtoupper($key)] = $value;
        }

        $results = array_merge(
            $results,
            $this->formatHttpHeadersIntoServerVariables($headers)
        );

        if (isset($results['REQUEST_URI'], $results['QUERY_STRING']) &&
            strlen($results['QUERY_STRING']) > 0 &&
            strpos($results['REQUEST_URI'], '?') === false) {
            $results['REQUEST_URI'] .= '?' . $results['QUERY_STRING'];
        }

        return $phpSapi === 'cli-server'
            ? $this->correctHeadersSetIncorrectlyByPhpDevServer($results)
            : $results;
    }

    /**
     * Format the given HTTP headers into properly formatted $_SERVER variables.
     *
     * @param array $headers
     * @return array
     */
    protected function formatHttpHeadersIntoServerVariables(array $headers): array
    {
        $results = [];

        foreach ($headers as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));

            if (!in_array($key, ['HTTPS', 'REMOTE_ADDR', 'SERVER_PORT'])) {
                $key = 'HTTP_' . $key;
            }

            $results[$key] = $value;
        }

        return $results;
    }

    /**
     * Correct headers set incorrectly by built-in PHP development server.
     *
     * @param array $headers
     * @return array
     */
    protected function correctHeadersSetIncorrectlyByPhpDevServer(array $headers): array
    {
        if (array_key_exists('HTTP_CONTENT_LENGTH', $headers)) {
            $headers['CONTENT_LENGTH'] = $headers['HTTP_CONTENT_LENGTH'];
        }

        if (array_key_exists('HTTP_CONTENT_TYPE', $headers)) {
            $headers['CONTENT_TYPE'] = $headers['HTTP_CONTENT_TYPE'];
        }

        return $headers;
    }
}
