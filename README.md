Laravel Octane Workerman
---

## Installing

```shell
$ composer require jie-anthony/laravel-octane-workerman:dev-master -vvv
```

### Configuration

`octane.php`

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

### Start

```shell
php artisan octane:workerman --port=9502 --host=0.0.0.0
```
