<?php

return [
    'memory_limit' => '512M',

    'http' => [
        'enable' => true,
        'host' => '0.0.0.0',
        'port' => 7050,
        'transport' => 'tcp',
        'context' => [],
        'name' => env('APP_NAME', 'laravel-workerman') . ' HttpWorker',
        'count' => cpu_count() * 3,
        'user' => '',
        'group' => '',
        'reusePort' => true,
        'pidFile' => storage_path('logs/laravel-workerman.pid'),
        'stdoutFile' => storage_path('logs/stdout.log'),
        'logFile' => storage_path('logs/workerman.log'),
        'maxPackageSize' => 10 * 1024 * 1024,
    ],
    'register' => [
        'enable' => false,
        'name' => env('APP_NAME', 'laravel-workerman') . ' RegisterWorker',
        'host' => '0.0.0.0',
        'port' => 7100,
    ],
    'gateway-websocket' => [
        'enable' => false,
        'protocol' => 'websocket', // @see https://www.workerman.net/doc/gateway-worker/gateway.html
        'host' => '0.0.0.0',
        'port' => 7200,
        'name' => env('APP_NAME', 'laravel-workerman') . ' WebsocketGatewayWorker',
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
        'enable' => false,
        'protocol' => 'tcp', // @see https://www.workerman.net/doc/gateway-worker/gateway.html
        'host' => '0.0.0.0',
        'port' => 7300,
        'name' => env('APP_NAME', 'laravel-workerman') . ' TcpGatewayWorker',
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
        'enable' => false,
        'protocol' => 'http', // @see https://www.workerman.net/doc/gateway-worker/business-worker.html
        'host' => '0.0.0.0',
        'port' => 7400,
        'name' => env('APP_NAME', 'laravel-workerman') . ' BusinessWorker',
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
            'enable' => false,
            'handler' => JieAnthony\LaravelOctaneWorkerman\Process\DatabaseHeartbeat::class,
            'reloadable' => false,
        ],
        'ddos-proxy-http' => [
            'enable' => false,
            'handler' => JieAnthony\LaravelOctaneWorkerman\Process\DdosProxyHttp::class,
            'listen' => 'tcp://0.0.0.0:7000',
            'context' => null,
        ],

        /**
         * @see https://www.workerman.net/doc/workerman/appendices/about-websocket.html 查看 websocket 文档
         * 
         * @see http://www.websocket-test.com/ 测试连接可用性
         * 
         * ws://x.x.x.x:3000 地址是 listen 配置的地址。可通过 nginx 反向代理隐藏端口
         * 
         * 接收消息使用 Events 类进行接收。生成 Events 类: php artisan make:process Events
         */
        env('APP_NAME', 'laravel-workerman') . ' WebsocketWorker' => [
            'enable' => false,
            'listen' => 'websocket://0.0.0.0:3000',
            'count' => cpu_count(),
            // 'user' => null,
            // 'group' => null,
            'reloadable' => true,
            'reusePort' => true,
            // 'transport' => 'tcp', // Transport layer protocol. default tcp
            // 'protocol' => null, // Application layer protocol.

            // process business by handler, worker_bind life cycle: https://github.com/mouyong/laravel-octane-workerman/blob/master/src/helpers.php#L243-L252
            'handler' => App\Events::class,
        ],

        'monitor' => [
            'enable' => env('APP_ENV') === 'local' && env('APP_DEBUG') === true,
            'handler' => JieAnthony\LaravelOctaneWorkerman\Process\Monitor::class,
            'reloadable' => false,
            'constructor' => [
                // Monitor these directories
                'monitor_dir' => [
                    base_path() . '/app',
                    base_path() . '/bootstrap',
                    base_path() . '/config',
                    base_path() . '/database',
                    base_path() . '/public/**/*.php',
                    base_path() . '/resources/**/*.php',
                    base_path() . '/routes',
                    base_path() . '/composer.lock',
                    base_path() . '/.env',
                    base_path() . '/lang',
                    base_path() . '/process',
                ],
                // Files with these suffixes will be monitored
                'monitor_extensions' => [
                    'php', 'html', 'htm', 'env'
                ]
            ]
        ],
    ],
];
