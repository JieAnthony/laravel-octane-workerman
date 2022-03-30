<?php

namespace JieAnthony\LaravelOctaneWorkerman;

use Illuminate\Support\ServiceProvider;
use JieAnthony\LaravelOctaneWorkerman\Commands\WorkermanGatewayWorkerMakeSocketsCommand;
use JieAnthony\LaravelOctaneWorkerman\Commands\WorkermanGatewayWorkerMakeEventsCommand;

class WorkermanGatewayWorkerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->publishes([
            $configPath = __DIR__.'/config/workerman.php' => config_path('workerman.php'),
        ], 'workerman-gatewayworker-config');

        $this->mergeConfigFrom(
            $configPath, 'workerman'
        );

        if (!config('octane.gatewayworker')) {
            config([
                'octane.gatewayworker' => config('workerman.gatewayworker'),
            ]);
        }
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WorkermanGatewayWorkerMakeSocketsCommand::class,
                WorkermanGatewayWorkerMakeEventsCommand::class,
            ]);
        }
    }
}
