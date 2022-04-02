<?php

namespace JieAnthony\LaravelOctaneWorkerman\Commands;

use Illuminate\Support\Str;
use Laravel\Octane\Commands\Command;
use Laravel\Octane\Commands\Concerns\InteractsWithServers;
use JieAnthony\LaravelOctaneWorkerman\Workerman\ServerProcessInspector;
use JieAnthony\LaravelOctaneWorkerman\Workerman\ServerStateFile;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class StartWorkermanGatewayCommand extends Command implements SignalableCommandInterface
{
    use Concerns\InstallsGatewayWorkerDependencies;
    use InteractsWithServers;

    /**
     * The command's signature.
     *
     * @var string
     */
    public $signature = 'workerman:gateway
                    {mode=start : Workerman server mode [ start | daemon | reload | stop ]}
                    {--host : The IP address the server should bind to}
                    {--port : The port the server should be available on}
                    {--max-requests=10000 : The number of requests to process before reloading the server}
                    {--watch : Automatically reload the server when the application is modified}
                    {--d|debug : Automatically reload the server when the application is modified}';

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

        if (in_array($this->argument('mode'), ['start', 'daemon'])) {
            $this->writeServerStateFile($serverStateFile, $this->isDaemon());
        }

        return match ($this->argument('mode')) {
            default => $this->error('Error workerman server mode'),
            'start', 'daemon' => $this->serverStart($inspector),
            'stop' => $this->serverStop($inspector),
            'reload' => $this->serverReload($inspector),
            'status' => $this->serverStatus($inspector),
        };
    }

    public function isDaemon()
    {
        return $this->argument('mode') === 'daemon';
    }

    protected function serverStart(ServerProcessInspector $inspector)
    {
        if ($inspector->serverIsRunning()) {
            $this->error('Workerman server is already running.');

            return Command::FAILURE;
        }

        if (!$this->isDaemon()) {
            return $this->runServer(
                $inspector->startServer(), 
                $inspector, 
                'workerman'
            );
        }

        $inspector->startDaemonServer();

        $this->info('The workerman daemon started successfully');
        return Command::SUCCESS;
    }

    protected function serverStatus(ServerProcessInspector $inspector)
    {
        $inspector->getServerStatus(function ($type, $data) {
            $this->output->write($data);
        }, $this->option('debug'));

        return Command::SUCCESS;
    }

    protected function serverReload(ServerProcessInspector $inspector)
    {
        $inspector->reloadServer();

        $this->info('The workerman server reload successfully');

        return Command::SUCCESS;
    }

    protected function serverStop(ServerProcessInspector $inspector)
    {
        $this->info('The workerman server stopped successfully');

        $inspector->stopServer();

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

    /**
     * Returns the list of signals to subscribe.
     *
     * @return array
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM, SIGHUP];
    }

    /**
     * The method will be called when the application is signaled.
     *
     * @param  int  $signal
     * @return void
     */
    public function handleSignal(int $signal): void
    {
        /** @var ServerProcessInspector $inspector */
        $inspector = app(ServerProcessInspector::class);

        if ($this->argument('mode') === 'status' && $this->option('debug') && $signal === SIGINT) {
            exit("\n");
        }

        if ($signal === SIGHUP) {
            $this->serverReload($inspector);
            return;
        }
        
        $this->serverStop($inspector);
    }
}
