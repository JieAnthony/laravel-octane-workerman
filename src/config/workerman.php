<?php

return [
    'gatewayworker' => [
        'http' => [
            'enable' => true,
            'host' => '0.0.0.0',
            'port' => 7000,
            'transport' => 'tcp',
            'context' => [],
            'name' => env('APP_NAME', 'laravel-octane-workerman') . ' HttpWorker',
            'count' => cpu_count() * 2,
            'user' => '',
            'group' => '',
            'reuse_port' => true,
            'pid_file' => storage_path('logs/laravel-octane-workerman.pid'),
            'stdout_file' => storage_path('logs/stdout.log'),
            'log_file' => storage_path('logs/workerman.log'),
            'max_package_size' => 10 * 1024 * 1024,
        ],
        'register' => [
            'enable' => true,
            'name' => env('APP_NAME', 'laravel-octane-workerman') . ' RegisterWorker',
            'host' => '0.0.0.0',
            'port' => 7100,
        ],
        'gateway-websocket' => [
            'enable' => true,
            'protocol' => 'websocket', // @see https://www.workerman.net/doc/gateway-worker/gateway.html
            'host' => '0.0.0.0',
            'port' => 7200,
            'name' => env('APP_NAME', 'laravel-octane-workerman') . ' WebsocketGatewayWorker',
            'count' => cpu_count(),
            'lanIp' => '127.0.0.1',
            'startPort' => 7201,
            'pingInterval' => 10,
            'pingData' => '{"type":"ping"}',
            'registerAddress' => '127.0.0.1:7100',
            'onWorkerStart' => null,
            'onWorkerStop' => null,
            'onConnect' => null,
            'onClose' => null,
            // 'onWorkerStart' => [App\Sockets::class, 'onWorkerStart'],
            // 'onWorkerStop' => [App\Sockets::class, 'onWorkerStop'],
            // 'onConnect' => [App\Sockets::class, 'onConnect'],
            // 'onClose' => [App\Sockets::class, 'onClose'],
        ],
        'gateway-tcp' => [
            'enable' => true,
            'protocol' => 'tcp', // @see https://www.workerman.net/doc/gateway-worker/gateway.html
            'host' => '0.0.0.0',
            'port' => 7300,
            'name' => env('APP_NAME', 'laravel-octane-workerman') . ' TcpGatewayWorker',
            'count' => cpu_count(),
            'lanIp' => '127.0.0.1',
            'startPort' => 7301,
            'pingInterval' => 10,
            'pingData' => '{"type":"ping"}',
            'registerAddress' => '127.0.0.1:7100',
            'onWorkerStart' => null,
            'onWorkerStop' => null,
            'onConnect' => null,
            'onClose' => null,
            // 'onWorkerStart' => [App\Sockets::class, 'onWorkerStart'],
            // 'onWorkerStop' => [App\Sockets::class, 'onWorkerStop'],
            // 'onConnect' => [App\Sockets::class, 'onConnect'],
            // 'onClose' => [App\Sockets::class, 'onClose'],
        ],
        'business' => [
            'enable' => true,
            'protocol' => 'http', // @see https://www.workerman.net/doc/gateway-worker/business-worker.html
            'host' => '0.0.0.0',
            'port' => 7400,
            'name' => env('APP_NAME', 'laravel-octane-workerman') . ' BusinessWorker',
            'count' => cpu_count() * 2,
            'registerAddress' => '127.0.0.1:7100',
            'onWorkerStart' => null,
            'onWorkerStop' => null,
            'eventHandler' => null,
            // 'onWorkerStart' => [App\Events::class, 'onWorkerStart'],
            // 'onWorkerStop' => [App\Events::class, 'onWorkerStop'],
            // @see https://www.workerman.net/doc/gateway-worker/business-worker.html
            // @see https://www.workerman.net/doc/gateway-worker/on-messsge.html
            // 'eventHandler' => App\Events::class,
        ],
        'process' => [
            'database-heartbeat' => [
                'enable' => true,
                'handler' => JieAnthony\LaravelOctaneWorkerman\Process\DatabaseHeartbeat::class,
                'reloadable' => false,
            ],
            // 'monitor' => [
            //     'enable' => false,
            //     'handler' => JieAnthony\LaravelOctaneWorkerman\Process\Monitor::class,
            //     'reloadable' => false,
            //     'constructor' => [
            //         // Monitor these directories
            //         'monitor_dir' => [
            //             base_path() . '/app',
            //             base_path() . '/bootstrap',
            //             base_path() . '/config',
            //             base_path() . '/database',
            //             base_path() . '/public/**/*.php',
            //             base_path() . '/resources/**/*.php',
            //             base_path() . '/routes',
            //             base_path() . '/composer.lock',
            //             base_path() . '/.env',
            //             base_path() . '/lang',
            //             base_path() . '/process',
            //         ],
            //         // Files with these suffixes will be monitored
            //         'monitor_extensions' => [
            //             'php', 'html', 'htm', 'env'
            //         ]
            //     ]
            // ],
        ],
    ],
];