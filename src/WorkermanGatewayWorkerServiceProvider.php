<?php

namespace JieAnthony\LaravelOctaneWorkerman;

use Illuminate\Support\ServiceProvider;
use JieAnthony\LaravelOctaneWorkerman\Commands\WorkermanGatewayMakeSocketsCommand;
use JieAnthony\LaravelOctaneWorkerman\Commands\WorkermanGatewayMakeEventsCommand;
use JieAnthony\LaravelOctaneWorkerman\Commands\WorkermanGatewayMakeCustomProcessCommand;

class WorkermanGatewayWorkerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->publishes([
            $configPath = __DIR__.'/../config/workerman.php' => config_path('workerman.php'),
        ], 'laravel-octane-workerman');

        $this->mergeConfigFrom(
            $configPath,
            'workerman'
        );

        if (!config('octane.workerman')) {
            config([
                'octane.workerman' => config('workerman'),
            ]);
        }

        Gateway::resolveRegisterAddress();
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WorkermanGatewayMakeSocketsCommand::class,
                WorkermanGatewayMakeEventsCommand::class,
                WorkermanGatewayMakeCustomProcessCommand::class,
            ]);
        }
    }
}
