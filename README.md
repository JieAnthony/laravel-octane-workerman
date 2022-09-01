Laravel Octane Workerman
---

[![Latest Stable Version](http://poser.pugx.org/jie-anthony/laravel-octane-workerman/v)](https://packagist.org/packages/jie-anthony/laravel-octane-workerman) [![Total Downloads](http://poser.pugx.org/jie-anthony/laravel-octane-workerman/downloads)](https://packagist.org/packages/jie-anthony/laravel-octane-workerman) [![Latest Unstable Version](http://poser.pugx.org/jie-anthony/laravel-octane-workerman/v/unstable)](https://packagist.org/packages/jie-anthony/laravel-octane-workerman) [![License](http://poser.pugx.org/jie-anthony/laravel-octane-workerman/license)](https://packagist.org/packages/jie-anthony/laravel-octane-workerman) [![PHP Version Require](http://poser.pugx.org/jie-anthony/laravel-octane-workerman/require/php)](https://packagist.org/packages/jie-anthony/laravel-octane-workerman)


## Discussions 

访问地址 https://laravel-workerman.iwnweb.com/


## Todos

- [ ] Support Workerman v5
- [x] add make Events command
- [x] add make Socket Command
- [x] add make CustomProcess command
- [x] add global setRegisterAddress of API method


## Screenshot

Start the laravel project through `gatewayworker` to make the development of laravel in the Internet of Things more convenient. Fetch and communicate with different gateways via API.

<details>
 <summary><b>See the Websocket Gateway and API signal communication <code>Screenshot</code></b></summary>
 

![image](https://user-images.githubusercontent.com/10336437/160743947-80837068-5ca6-4ee7-a560-d108878fedbd.png)

![image](https://user-images.githubusercontent.com/10336437/160744007-8d0c4af3-487a-41a8-8f9c-bb7bcf4ad118.png)

![image](https://user-images.githubusercontent.com/10336437/160744127-979c1531-858e-4869-9ccf-a3b02e582091.png)

![image](https://user-images.githubusercontent.com/10336437/160744093-f6c4020a-fbb9-4bf7-a420-0078f354c53c.png)

![image](https://user-images.githubusercontent.com/10336437/161367556-01f4cdb5-c51f-4afa-9875-63ca09d83dd7.jpg)
</details>

## Installing

```shell
$ composer config repositories.laravel-octane-workerman vcs https://github.com/mouyong/laravel-octane-workerman

# support workerman:gateway and workerman:http command install from https://github.com/mouyong/laravel-octane-workerman
$ composer require jie-anthony/laravel-octane-workerman:dev-master -vvv

# just support octane:workerman command, install from https://github.com/JieAnthony/laravel-octane-workerman
$ composer require jie-anthony/laravel-octane-workerman -vvv
```


## Configuration

```shell
php artisan vendor:publish --provider="Laravel\Octane\OctaneServiceProvider"
php artisan vendor:publish --provider="JieAnthony\LaravelOctaneWorkerman\WorkermanGatewayWorkerServiceProvider"
```

configuration edit in `config/workerman.php`

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
php artisan worker start
php artisan worker daemon
php artisan worker reload
php artisan worker stop
php artisan worker status -d

php artisan make:sockets Sockets
php artisan make:events Events
php artisan make:process CustomProcess
```

### webman plugin useage

* [webman plugin](webman_plugin_require_example/README.md)


## Documentation

* [Workerman](https://www.workerman.net/doc/workerman/)


## websockets

The tcp `ddos-proxy-http` address

`ws://127.0.0.1:7000/ws`

```
location /ws {
    # the websocket address with http protocol
    proxy_pass http://127.0.0.1:7200;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'Upgrade';
}
```


### Thanks

* [Workerman](https://github.com/walkor/Workerman)
* [Laravel](https://github.com/laravel/laravel)
* [Octane](https://github.com/laravel/octane)
* [laravel-octane-workerman](https://github.com/JieAnthony/laravel-octane-workerman)

## Contact

Join QQ Group <a target="_blank" href="https://qm.qq.com/cgi-bin/qm/qr?k=gGezeVnF0yXZjkg_cmBjXojE__v38NbU&jump_from=webapi"><img border="0" src="images/group.png" alt="laravel-octane-gatewayworker" title="laravel-octane-gatewayworker"> 650057913</a>

<img src="images/laravel-octane-gatewayworker group qrcode.png" alt="laravel-octane-gatewayworker 群聊二维码" />


## License

MIT
