<?php

namespace JieAnthony\LaravelOctaneWorkerman\Workerman;

use Laravel\Octane\SymfonyProcessFactory;
use Symfony\Component\Process\PhpExecutableFinder;
use Workerman\Worker;

class ServerProcessInspector
{
    public function __construct(
        protected ServerStateFile       $serverStateFile,
        protected SymfonyProcessFactory $processFactory
    ) {
    }

    /**
     * Determine if the Workerman server process is running.
     *
     * @return bool
     */
    public function serverIsRunning(): bool
    {
        ['masterProcessId' => $masterProcessId] = $this->serverStateFile->read();

        return (bool) $masterProcessId;
    }

    public function writeProcessId()
    {
        $pid = @file_get_contents(config('workerman.http.pidFile'));

        if ($pid) {
            $this->serverStateFile->writeProcessId($pid);
            return true;
        }

        return false;
    }

    public function getServer($mode, array $args = [])
    {
        $command = [
            (new PhpExecutableFinder())->find(), 
            'gatewayworker-server', 
            $mode, 
            $this->serverStateFile->path(), 
            ...$args,
        ];

        $cwd = realpath(__DIR__ . '/../../bin');

        $env = [
            'APP_BASE_PATH' => base_path(),
            'LARAVEL_OCTANE' => 1,
        ];

        return $this->processFactory->createProcess(
            command: $command, 
            cwd: $cwd, 
            env: $env, 
            input: null, 
            timeout: null
        );
    }

    /**
     * Start the Workerman workers.
     *
     * @return \Symfony\Component\Process\Process
     */
    public function startServer()
    {
        if ($this->serverIsRunning()) {
            $this->stopServer();

            return;
        }

        $server = $this->getServer('start');

        $server->start();

        $server->waitUntil([$this, 'writeProcessId']);

        $this->writeProcessId($server->getPid());

        return $server;
    }

    /**
     * Start the Workerman workers as daemon mode.
     *
     * @return \Symfony\Component\Process\Process
     */
    public function startDaemonServer()
    {
        if ($this->serverIsRunning()) {
            return;
        }

        $server = $this->getServer('start', ['-d']);

        $server->start();

        $server->waitUntil([$this, 'writeProcessId']);

        return $server;
    }

    /**
     * Reload the Workerman workers.
     *
     * @return void
     */
    public function reloadServer()
    {
        return $this->getServer('reload')->run();
    }

    /**
     * Stop the Workerman server.
     *
     * @return void
     */
    public function stopServer(): void
    {
        $this->getServer('stop')->run();

        $this->serverStateFile->delete();

        Worker::stopAll();
    }

    /**
     * Get the Workerman server status.
     *
     * @return \Symfony\Component\Process\Process
     */
    public function getServerStatus(callable $callable, bool $debug = false)
    {
        if (!$this->serverIsRunning()) {
            echo "Workerman not running\n";
            return;
        }

        $debug = $debug ? '-d' : null;

        $server = $this->getServer('status', [$debug]);

        $server->start();

        return $server->waitUntil($callable);
    }
}
