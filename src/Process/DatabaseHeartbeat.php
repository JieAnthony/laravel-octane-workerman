<?php

namespace JieAnthony\LaravelOctaneWorkerman\Process;

use Workerman\Timer;
use Workerman\Worker;
use Illuminate\Support\Facades\DB;

/**
 * Class DatabaseHeartbeat
 * @package Process
 */

 class DatabaseHeartbeat
 {
     public function onWorkerStart(Worker $worker)
     {
         $connections = config('database.connections');
         if (!$connections) {
             return;
         }

         // Heartbeat
         Timer::add(55, function () use ($connections) {
             foreach ($connections as $key => $item) {
                 if ($item['driver'] == 'mysql') {
                     DB::connection($key)->select('select 1');
                 }
             }
         });
     }
 }
