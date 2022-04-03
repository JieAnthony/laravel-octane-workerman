# 在 laravel 框架中引入 webman plugin

## 1. 添加插件安装脚本到项目中

在项目的 `composer.json` 添加如下内容。可参考 `laravel-octane-workerman` 的 `composer.json` 中，`scripts` 配置

```json
{
    // ...
    "scripts": {
        "post-package-install": [
            "JieAnthony\\LaravelOctaneWorkerman\\WebmanPlugin::install"
        ],
        "post-package-update": [
            "JieAnthony\\LaravelOctaneWorkerman\\WebmanPlugin::install"
        ],
        "pre-package-uninstall": [
            "JieAnthony\\LaravelOctaneWorkerman\\WebmanPlugin::uninstall"
        ],
        // ...
    }
    // ...
}
```

## 2. 引入 webman 插件

```
composer require webman/push -vvv
```

## 3. 适配 laravel 框架

### 1. 路由适配

- 修改插件的 `route.php` 文件
- 将 `Webman\Request` 替换为 `Illuminate\Http\Request`
- 将 `Webman\Route` 替换为 `Illuminate\Routing\Router`
- 使用 laravel 的路由分组包含路由
```php
app('router')->middleware(['web'])->group(function (Router $route) {
    // ...
});
```
- 替换 `Route::` 调用为 `$route->`
```php
app('router')->middleware(['web'])->group(function (Router $route) {
    /**
     * 推送js客户端文件
     */
    // here before
    // Route::any('plugin/webman/push/push.js', function (Request $request) {
    //    return response()->file(base_path() . '/vendor/webman/push/src/push.js');
    // });

    // here after
    $route->any('plugin/webman/push/push.js', function (Request $request) {
        return response()->file(base_path() . '/vendor/webman/push/src/push.js');
    });
});
```