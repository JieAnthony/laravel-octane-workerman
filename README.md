Laravel Octane Workerman
---

## Installing

```shell
$ composer require "jie-anthony/laravel-octane-workerman:^1.0" -vvv
```

## Configuration

```shell
php artisan vendor:publish --provider="Laravel\Octane\OctaneServiceProvider"
```

configuration write in `octane.php`

```php
'gatewayworker' => [
    'http' => [
        'enable' => true,
        'host' => '0.0.0.0',
        'port' => 7000,
        'transport' => 'tcp',
        'context' => [],
        'name' => env('APP_NAME', 'laravel-octane-workerman') . 'HttpWorker',
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
        'name' => env('APP_NAME', 'laravel-octane-workerman') . 'RegisterWorker',
        'host' => '0.0.0.0',
        'port' => 7100,
    ],
    'gateway-websocket' => [
        'enable' => true,
        'protocol' => 'websocket', // @see https://www.workerman.net/doc/gateway-worker/gateway.html
        'host' => '0.0.0.0',
        'port' => 7200,
        'name' => env('APP_NAME', 'laravel-octane-workerman') . 'WebsocketGatewayWorker',
        'count' => cpu_count(),
        'lanIp' => '127.0.0.1',
        'startPort' => 7201,
        'pingInterval' => 10,
        'pingData' => '{"type":"ping"}',
        'registerAddress' => '127.0.0.1:7000',
        'onWorkerStart' => null,
        'onWorkerStop' => null,
        // 'onConnect' => [App\Websocket::class, 'onConnect'],
        // 'onClose' => [App\Websocket::class, 'onClose'],
    ],
    'gateway-tcp' => [
        'enable' => true,
        'protocol' => 'tcp', // @see https://www.workerman.net/doc/gateway-worker/gateway.html
        'host' => '0.0.0.0',
        'port' => 7300,
        'name' => env('APP_NAME', 'laravel-octane-workerman') . 'TcpGatewayWorker',
        'count' => cpu_count(),
        'lanIp' => '127.0.0.1',
        'startPort' => 7301,
        'pingInterval' => 10,
        'pingData' => '{"type":"ping"}',
        'registerAddress' => '127.0.0.1:7000',
        'onWorkerStart' => null,
        'onWorkerStop' => null,
        // 'onConnect' => [App\Websocket::class, 'onConnect'],
        // 'onClose' => [App\Websocket::class, 'onClose'],
    ],
    'business' => [
        'enable' => true,
        'protocol' => 'http', // @see https://www.workerman.net/doc/gateway-worker/business-worker.html
        'host' => '0.0.0.0',
        'port' => 7400,
        'name' => env('APP_NAME', 'laravel-octane-workerman') . 'BusinessWorker',
        'count' => cpu_count() * 2,
        'registerAddress' => '127.0.0.1:7000',
        // 'onWorkerStart' => [App\Events::class, 'onWorkerStart'],
        'onWorkerStop' => null,
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
```

## Command parameter

| option                   | default |
|--------------------------|---------|
| host                     | 0.0.0.0 |
| port                     | 8000    |
| max-requests             | 10000   |
| mode  | start   |
| watch                    |         |

mode options : ( start / daemon / stop )

## Useage

```shell
php artisan workerman:gateway --port=9502 --host=0.0.0.0 start
php artisan workerman:gateway --port=9502 --host=0.0.0.0 daemon
php artisan workerman:gateway start
php artisan workerman:gateway daemon
php artisan workerman:gateway reload
php artisan workerman:gateway stop

php artisan workerman:http --port=9502 --host=0.0.0.0 start
php artisan workerman:http --port=9502 --host=0.0.0.0 daemon
php artisan workerman:http start
php artisan workerman:http daemon
php artisan workerman:http reload
php artisan workerman:http stop
```

## Documentation

* [Workerman](https://www.workerman.net/doc/workerman/)

### Thanks

* [Workerman](https://github.com/walkor/Workerman)
* [Laravel](https://github.com/laravel/laravel)
* [Octane](https://github.com/laravel/octane)

### TODO
* Support Workerman v5  

## License

MIT
