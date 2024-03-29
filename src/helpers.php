<?php

require_once __DIR__ . '/webman_helpers.php';

if (!function_exists('get_variable_name')) {
    function get_variable_name(&$var, $scope = null)
    {
        $scope = $scope == null ? $GLOBALS : $scope; // 如果没有范围则在globals中找寻

        // 因有可能有相同值的变量,因此先将当前变量的值保存到一个临时变量中,然后再对原变量赋唯一值,以便查找出变量的名称,找到名字后,将临时变量的值重新赋值到原变量
        $tmp = $var;

        $var = 'tmp_value_' . mt_rand();

        $name = array_search($var, $scope, true); // 根据值查找变量名称

        $var = $tmp;
        if (!$name) {
            return lcfirst(get_variable_name_of_class($var));
        }

        unset($tmp);

        return $name ?? null;
    }
}

if (!function_exists('get_variable_name_of_class')) {
    function get_variable_name_of_class($var)
    {
        if (is_scalar($var)) {
            return null;
        }

        $class = get_class($var);

        $varName = str_replace('\\', '', $class);

        return lcfirst($varName);
    }
}

if (!function_exists('is_phar')) {
    /**
     * @return bool
     */
    function is_phar()
    {
        return class_exists(\Phar::class, false) && Phar::running();
    }
}

if (!function_exists('app_path')) {
    /**
     * @return string
     */
    function app_path()
    {
        return base_path() . DIRECTORY_SEPARATOR . 'app';
    }
}

if (!function_exists('config_path')) {
    /**
     * @return string
     */
    function config_path()
    {
        return base_path() . DIRECTORY_SEPARATOR . 'config';
    }
}

if (!function_exists('copy_dir')) {
    /**
     * Copy dir.
     * @param $source
     * @param $dest
     * @param bool $overwrite
     * @return void
     */
    function copy_dir($source, $dest, $overwrite = false)
    {
        if (is_dir($source)) {
            if (!is_dir($dest)) {
                mkdir($dest);
            }
            $files = scandir($source);
            foreach ($files as $file) {
                if ($file !== "." && $file !== "..") {
                    copy_dir("$source/$file", "$dest/$file");
                }
            }
        } else if (file_exists($source) && ($overwrite || !file_exists($dest))) {
            copy($source, $dest);
        }
    }
}

if (!function_exists('remove_dir')) {

    /**
     * Remove dir.
     * @param $dir
     * @return bool
     */
    function remove_dir($dir)
    {
        if (is_link($dir) || is_file($dir)) {
            return unlink($dir);
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file") && !is_link($dir)) ? remove_dir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

if (!function_exists('webman_config')) {
    /**
     * @return array|mixed|null
     */
    function webman_config(string $key = null, $default = null)
    {
        return \JieAnthony\LaravelOctaneWorkerman\WebmanConfig::get($key, $default);
    }
}

if (!function_exists('cpu_count')) {
    /**
     * @return int
     */
    function cpu_count()
    {
        // Windows does not support the number of processes setting.
        if (\DIRECTORY_SEPARATOR === '\\') {
            return 1;
        }

        if (strtolower(PHP_OS) === 'darwin') {
            $count = shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = shell_exec('nproc');
        }

        $count = (int)$count > 0 ? (int)$count : 4;

        return $count;
    }
}

if (!function_exists('create_laravel_application_for_worker')) {
    /**
     * 在 worker 内部引入 laravel 与 webman_config
     *
     * @param  \Workerman\Worker|\Laravel\Octane\Worker|null $worker
     * @return void
     */
    function create_laravel_application_for_worker($worker)
    {
        defined('LARAVEL_WORKERMAN_START') or define('LARAVEL_WORKERMAN_START', microtime());

        require_once $_SERVER['APP_BASE_PATH'] . '/vendor/laravel/octane/bin/bootstrap.php';

        $worker->app = (new \Laravel\Octane\ApplicationFactory($_SERVER['APP_BASE_PATH']))->createApplication();

        webman_bootstrap($worker);
    }
}

if (!function_exists('request_bind_connection')) {
    /**
     * @return void
     */
    function request_bind_connection()
    {
        \JieAnthony\LaravelOctaneWorkerman\WebmanRequest::bindInstance(...func_get_args());
    }
}

if (!function_exists('response_bind_connection')) {
    /**
     * @return void
     */
    function response_bind_connection()
    {
        \JieAnthony\LaravelOctaneWorkerman\WebmanResponse::bindInstance(...func_get_args());
    }
}

if (!function_exists('webman_bootstrap')) {
    /**
     * @param  \Workerman\Worker|\Laravel\Octane\Worker|null $worker
     * @return void
     */
    function webman_bootstrap($worker = null)
    {
        \JieAnthony\LaravelOctaneWorkerman\WebmanConfig::load(config_path(), ['container']);

        require_once __DIR__ . '/webman_bootstrap.php';

        webman_route_load();
    }
}

if (!function_exists('webman_route_load')) {
    /**
     * @return void
     */
    function webman_route_load(?string $pluginName = null, $autoload = true)
    {
        defined('LARAVEL_ROUTE_START') or define('LARAVEL_ROUTE_START', microtime());

        foreach (webman_config('plugin', []) as $firm => $projects) {
            foreach ($projects as $name => $project) {
                if ($pluginName && $name !== $pluginName) {
                    continue;
                }

                $file = config_path() . "/plugin/$firm/$name/route.php";

                if (!file_exists($file)) {
                    continue;
                }

                if (!$autoload) {
                    return $file;
                }

                require_once $file;
            }
        }
    }
}

if (!function_exists('worker_bind')) {
    /**
     * @param $worker
     * @param $class
     */
    function worker_bind($worker, $class)
    {
        $callback_map = [
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
            'onWorkerStop',
            'onWebSocketConnect'
        ];

        foreach ($callback_map as $name) {
            if (method_exists($class, $name)) {
                $worker->$name = [$class, $name];
            }
        }

        if (method_exists($class, 'onWorkerStart')) {
            call_user_func([$class, 'onWorkerStart'], $worker);
        }
    }
}

if (!function_exists('worker_start')) {
    /**
     * @param $process_name
     * @param $config
     * @return void
     */
    function worker_start($process_name, $config)
    {
        $worker = new \Workerman\Worker($config['listen'] ?? null, $config['context'] ?? []);
        $property_map = [
            'count',
            'user',
            'group',
            'reloadable',
            'reusePort',
            'transport',
            'protocol',
        ];

        $worker->name = $process_name;

        foreach ($property_map as $property) {
            if (isset($config[$property])) {
                $worker->$property = $config[$property];
            }
        }

        $worker->onWorkerStart = function ($worker) use ($config) {
            create_laravel_application_for_worker($worker);

            foreach ($config['services'] ?? [] as $server) {
                if (!class_exists($server['handler'])) {
                    echo "process error: class {$server['handler']} not exists\r\n";
                    continue;
                }

                $listen = new \Workerman\Worker($server['listen'] ?? null, $server['context'] ?? []);
                if (isset($server['listen'])) {
                    echo "listen: {$server['listen']}\n";
                }

                $instance = \Illuminate\Container\Container::getInstance()->make($server['handler'], $server['constructor'] ?? []);

                worker_bind($listen, $instance);

                $listen->listen();
            }

            if (isset($config['handler'])) {
                if (!class_exists($config['handler'])) {
                    echo "process error: class {$config['handler']} not exists\r\n";
                    return;
                }

                $instance = \Illuminate\Container\Container::getInstance()->make($config['handler'], $config['constructor'] ?? []);

                worker_bind($worker, $instance);
            }
        };
    }
}
