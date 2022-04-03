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

    public function writeProcessId($pid)
    {
        $this->serverStateFile->writeProcessId($pid);
    }

    public function getServer($mode, array $args = [])
    {
        return $this->processFactory->createProcess([
            (new PhpExecutableFinder())->find(), 'gatewayworker-server', $mode, $this->serverStateFile->path(), ...$args,
        ], realpath(__DIR__ . '/../../bin'), ['APP_BASE_PATH' => base_path(), 'LARAVEL_OCTANE' => 1], null, null);
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

        $server = $this->getServer('start');

        $server->run();

        $pid = file_get_contents(config('workerman.gatewayworker.http.pid_file'));

        $this->writeProcessId($pid);

        return $server;
    }

    /**
     * Reload the Workerman workers.
     *
     * @return void
     */
    public function reloadServer()
    {
        return $this->getServer('reload')->start();
    }

    /**
     * Stop the Workerman server.
     *
     * @return void
     */
    public function stopServer(): void
    {
        $this->getServer('stop')->start();

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

        return $server->run($callable);
    }
}
