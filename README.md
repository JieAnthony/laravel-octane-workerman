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
'workerman' => [
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
]
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
php artisan octane:workerman --port=9502 --host=0.0.0.0 start
php artisan octane:workerman --port=9502 --host=0.0.0.0 daemon
php artisan octane:workerman reload
php artisan octane:workerman stop
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
