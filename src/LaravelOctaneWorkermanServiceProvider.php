<?php

namespace JieAnthony\LaravelOctaneWorkerman;

use GatewayClient\Gateway;
use Illuminate\Support\ServiceProvider;
use JieAnthony\LaravelOctaneWorkerman\Commands\StartWorkermanGatewayHttpCommand;
use JieAnthony\LaravelOctaneWorkerman\Commands\StartWorkermanGatewayCommand;
use JieAnthony\LaravelOctaneWorkerman\Workerman\ServerProcessInspector as WorkermanServerProcessInspector;
use JieAnthony\LaravelOctaneWorkerman\Workerman\ServerStateFile as WorkermanServerStateFile;
use Laravel\Octane\SymfonyProcessFactory;

class LaravelOctaneWorkermanServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(WorkermanServerProcessInspector::class, function ($app) {
            return new WorkermanServerProcessInspector(
                $app->make(WorkermanServerStateFile::class),
                new SymfonyProcessFactory(),
            );
        });

        $this->app->bind(WorkermanServerStateFile::class, function ($app) {
            return new WorkermanServerStateFile($app['config']->get(
                'octane.state_file',
                storage_path('logs/octane-server-state.json')
            ));
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                StartWorkermanGatewayHttpCommand::class,
                StartWorkermanGatewayCommand::class,
            ]);
        }
    }
}
