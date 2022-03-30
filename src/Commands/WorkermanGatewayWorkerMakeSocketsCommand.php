<?php

namespace JieAnthony\LaravelOctaneWorkerman\Commands;

use Illuminate\Console\Command;

use Illuminate\Console\GeneratorCommand;

class WorkermanGatewayWorkerMakeSocketsCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman:gatewayworker-make-sockets {name : The name of the Sockets}
    {--path= : The location where the migration file should be created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Sockets class';

    protected function getStub(): string
    {
        if (file_exists(base_path('stubs/Sockets.stub'))) {
            return base_path('stubs/Sockets.stub');
        }

        return __DIR__.'/stubs/Sockets.stub';
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
