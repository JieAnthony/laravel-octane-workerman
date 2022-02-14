Laravel Octane Workerman
---

## Installing

```shell
$ composer require jie-anthony/laravel-octane-workerman:dev-master -vvv
```

## Configuration

```shell
php artisan vendor:publish --provider="Laravel\Octane\OctaneServiceProvider"
```

configuration write in `octane.php`

```php
'workerman' => [
    'transport' => 'tcp',
    'context' => [],
    'name' => env('APP_NAME', 'laravel-octane-workerman'),
    'count' => cpu_count() * 2,
    'user' => '',
    'group' => '',
    'reuse_port' => true,
    'pid_file' => storage_path('logs/webman.pid'),
    'stdout_file' => storage_path('logs/stdout.log'),
    'log_file' => storage_path('logs/workerman.log'),
    'max_package_size' => 10 * 1024 * 1024,
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

## Start

```shell
php artisan octane:workerman --port=9502 --host=0.0.0.0 --mode=start
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
