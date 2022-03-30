<?php

namespace JieAnthony\LaravelOctaneWorkerman\Commands;

use Illuminate\Console\GeneratorCommand;

class WorkermanGatewayWorkerMakeEventsCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman:gatewayworker-make-events {name : The name of the Events}
        {--path= : The location where the migration file should be created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Events class';

    protected function getStub(): string
    {
        if (file_exists(base_path('stubs/Events.stub'))) {
            return base_path('stubs/Events.stub');
        }

        return __DIR__.'/stubs/Events.stub';
    }

    protected function getPath($name)
    {
        if ($path = $this->option('path')) {
            $name = \Str::replaceFirst($this->rootNamespace(), '', $name);
            $filename = str_replace('\\', '/', $name);

            $path = trim($path, '/');

            return sprintf('%s/%s/%s.php',
                $this->laravel['path.base'],
                $path,
                $filename
            );
        }

        return parent::getPath($name);
    }
}
