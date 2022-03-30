<?php

namespace JieAnthony\LaravelOctaneWorkerman\Commands;

use Illuminate\Console\Command;

class WorkermanGatewayWorkerMakeEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman:gatewayworker-make-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Sockets class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
