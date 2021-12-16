<?php

namespace JieAnthony\LaravelOctaneWorkerman\Commands;

use Illuminate\Support\Str;
use Laravel\Octane\Commands\Command;
use Laravel\Octane\Commands\Concerns\InteractsWithServers;
use JieAnthony\LaravelOctaneWorkerman\Workerman\ServerProcessInspector;
use JieAnthony\LaravelOctaneWorkerman\Workerman\ServerStateFile;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class StartWorkermanCommand extends Command implements SignalableCommandInterface
{
    use Concerns\InstallsWorkermanDependencies;
    use InteractsWithServers;

    /**
     * The command's signature.
     *
     * @var string
     */
    public $signature = 'octane:workerman
                    {--host=0.0.0.0 : The IP address the server should bind to}
                    {--port=8000 : The port the server should be available on}
                    {--max-requests=10000 : The number of requests to process before reloading the server}
                    {--watch : Automatically reload the server when the application is modified}';

    /**
     * The command's description.
     *
     * @var string
     */
    public $description = 'Start the Octane Workerman server';

    public function handle(ServerProcessInspector $inspector, ServerStateFile $serverStateFile)
    {
        if (!$this->ensureWorkermanPackageIsInstalled()) {
            return 1;
        }

        if ($inspector->serverIsRunning()) {
            $this->error('Workerman server is already running.');

            return 1;
        }

        $this->writeServerStateFile($serverStateFile);

        $server = tap(
            new Process(
            [
                (new PhpExecutableFinder())->find(),
                'workerman-server',
                'start',
                $serverStateFile->path()
            ],
            realpath(__DIR__ . '/../../bin'),
            ['APP_BASE_PATH' => base_path(), 'LARAVEL_OCTANE' => 1],
            null,
            null
        )
        )->start();

        $serverStateFile->writeProcessId($server->getPid());

        return $this->runServer($server, $inspector, 'workerman');
    }

    /**
     * Write the RoadRunner server state file.
     *
     * @param \Laravel\Octane\RoadRunner\ServerStateFile $serverStateFile
     * @return void
     */
    protected function writeServerStateFile(ServerStateFile $serverStateFile)
    {
        $serverStateFile->writeState([
            'host' => $this->option('host'),
            'port' => $this->option('port'),
            'maxRequests' => $this->option('max-requests'),
            'octaneConfig' => config('octane'),
            'publicPath' => public_path(),
            'storagePath' => storage_path(),
        ]);
    }

    /**
     * Write the server process output to the console.
     *
     * @param \Symfony\Component\Process\Process $server
     * @return void
     */
    protected function writeServerOutput($server)
    {
        [$output, $errorOutput] = $this->getServerOutput($server);

        Str::of($output)
            ->explode("\n")
            ->filter()
            ->each(
                fn ($o) => is_array($stream = json_decode($o, true))
                ? $this->handleStream($stream)
                : $this->raw($o)
            );

        Str::of($errorOutput)
            ->explode("\n")
            ->filter()
            ->each(
                fn ($e) => is_array($stream = json_decode($e, true))
                ? $this->handleStream($stream)
                : $this->error($e)
            );
    }

    /**
     * Stop the server.
     *
     * @return int
     */
    protected function stopServer()
    {
        $inspector = app(ServerProcessInspector::class);

        if (!$inspector->serverIsRunning()) {
            app(ServerStateFile::class)->delete();

            $this->error('Workerman server is not running.');

            return 1;
        }

        $this->info('Stopping server...');

        app(ServerStateFile::class)->delete();

        $inspector->stopServer();

        return 0;
    }
}
