Laravel Octane Workerman
---

## Todos

- [ ] Support Workerman v5
- [ ] add make Events command
- [ ] add make Socket Command
- [ ] add make CustomProcess command
- [ ] add global setRegisterAddress of API method

## Screenshot

Start the laravel project through `gatewayworker` to make the development of laravel in the Internet of Things more convenient. Fetch and communicate with different gateways via API.

<details>
 <summary><b>See the Websocket Gateway and API signal communication <code>Screenshot</code></b></summary>
 

![image](https://user-images.githubusercontent.com/10336437/160743947-80837068-5ca6-4ee7-a560-d108878fedbd.png)

![image](https://user-images.githubusercontent.com/10336437/160744007-8d0c4af3-487a-41a8-8f9c-bb7bcf4ad118.png)

![image](https://user-images.githubusercontent.com/10336437/160744127-979c1531-858e-4869-9ccf-a3b02e582091.png)

![image](https://user-images.githubusercontent.com/10336437/160744093-f6c4020a-fbb9-4bf7-a420-0078f354c53c.png)
</details>

## Installing

```shell
$ composer config repositories.0 vcs https://github.com/mouyong/laravel-octane-workerman

# support workerman:gateway and workerman:http command install from https://github.com/mouyong/laravel-octane-workerman
$ composer require jie-anthony/laravel-octane-workerman:dev-gatewayworker -vvv

# just support octane:workerman command, install from https://github.com/JieAnthony/laravel-octane-workerman
$ composer require jie-anthony/laravel-octane-workerman -vvv
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
* [laravel-octane-workerman](https://github.com/JieAnthony/laravel-octane-workerman)

## Contact

Join QQ Group <a target="_blank" href="https://qm.qq.com/cgi-bin/qm/qr?k=gGezeVnF0yXZjkg_cmBjXojE__v38NbU&jump_from=webapi"><img border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="laravel-octane-gatewayworker" title="laravel-octane-gatewayworker"> 650057913</a>

<img src="images/laravel-octane-gatewayworker group qrcode.png" alt="laravel-octane-gatewayworker 群聊二维码" />


## License

MIT
