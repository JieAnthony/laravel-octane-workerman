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

        return (bool)$masterProcessId;
    }

    /**
     * Reload the Workerman workers.
     *
     * @return void
     */
    public function reloadServer($server = 'workerman-server'): void
    {
        $this->processFactory->createProcess([
            (new PhpExecutableFinder())->find(), $server, 'reload', $this->serverStateFile->path(),
        ], realpath(__DIR__ . '/../../bin'), ['APP_BASE_PATH' => base_path(), 'LARAVEL_OCTANE' => 1], null, null)->run();
    }

    /**
     * Stop the Workerman server.
     *
     * @return bool
     */
    public function stopServer(): bool
    {
        Worker::stopAll();

        return true;
    }
}
