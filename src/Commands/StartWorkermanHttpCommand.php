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

class StartWorkermanHttpCommand extends Command implements SignalableCommandInterface
{
    use Concerns\InstallsWorkermanDependencies;
    use InteractsWithServers;

    /**
     * The command's signature.
     *
     * @var string
     */
    public $signature = 'workerman:http
                    {mode=start : Workerman server mode [ start | daemon | reload | stop ]}
                    {--host : The IP address the server should bind to}
                    {--port : The port the server should be available on}
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

        if (!$this->option('host')) {
            $this->input->setOption('host', config('octane.gatewayworker.http.host'));
        }

        if (!$this->option('port')) {
            $this->input->setOption('port', config('octane.gatewayworker.http.port'));
        }

        if (!$this->option('host')) {
            $this->input->setOption('host', config('octane.gatewayworker.http.host'));
        }

        if (!$this->option('port')) {
            $this->input->setOption('port', config('octane.gatewayworker.http.port'));
        }

        return match ($mode = $this->argument('mode')) {
            default => $this->error('Error workerman server mode'),
            'start', 'daemon' => $this->serverStart($inspector, $serverStateFile, $mode == 'daemon'),
            'reload' => $this->serverReload($inspector),
            'stop' => $this->serverStop($serverStateFile)
        };
    }

    protected function serverStart(ServerProcessInspector $inspector, ServerStateFile $serverStateFile, bool $daemon)
    {
        if ($inspector->serverIsRunning()) {
            $this->error('Workerman server is already running.');

            return Command::FAILURE;
        }

        $this->writeServerStateFile($serverStateFile, $daemon);

        $server = new Process(
            [
                (new PhpExecutableFinder())->find(),
                'workerman-server',
                'start',
                $serverStateFile->path(),
                base_path()
            ],
            realpath(__DIR__ . '/../../bin'),
            ['APP_BASE_PATH' => base_path(), 'LARAVEL_OCTANE' => 1],
            null,
            null
        );

        if ($daemon) {
            $server->run();

            $this->info('The workerman daemon started successfully');

            return Command::SUCCESS;
        } else {
            $server->start();

            $serverStateFile->writeProcessId($server->getPid());

            return $this->runServer($server, $inspector, 'workerman');
        }
    }

    protected function serverReload(ServerProcessInspector $inspector)
    {
        $inspector->reloadServer();

        $this->info('The workerman server reload successfully');

        return Command::SUCCESS;
    }

    protected function serverStop(ServerStateFile $serverStateFile)
    {
        if (!file_exists($serverStateFile->path())) {
            $this->writeServerStateFile($serverStateFile, true);
        }

        tap(
            new Process(
                [
                    (new PhpExecutableFinder())->find(),
                    'workerman-server', 'stop',
                    $serverStateFile->path(),
                    base_path()
                ],
                realpath(__DIR__ . '/../../bin'),
                [
                    'APP_BASE_PATH' => base_path(),
                    'LARAVEL_OCTANE' => 1
                ],
                null,
                null
            )
        )->run();

        $serverStateFile->delete();

        $this->info('The workerman server stopped successfully');

        return Command::SUCCESS;
    }

    protected function writeServerStateFile(ServerStateFile $serverStateFile, bool $daemon = false)
    {
        $serverStateFile->writeState([
            'host' => $this->option('host'),
            'port' => $this->option('port'),
            'daemon' => $daemon,
            'maxRequests' => $this->option('max-requests'),
            'octaneConfig' => config('octane'),
            'publicPath' => public_path(),
            'storagePath' => storage_path(),
            'timezone' => config('app.timezone')
        ]);
    }

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

    protected function stopServer()
    {
        /** @var ServerProcessInspector $inspector */
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
