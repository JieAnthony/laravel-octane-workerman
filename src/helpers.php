<?php

if (!function_exists('webman_config')) {
    /**
     * @return int
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
     * @param  \Workerman\Worker $worker
     * @return void
     */
    function create_laravel_application_for_worker(\Workerman\Worker $worker)
    {
        require_once $_SERVER['APP_BASE_PATH'] . '/vendor/laravel/octane/bin/bootstrap.php';

        $worker->app = (new \Laravel\Octane\ApplicationFactory($_SERVER['APP_BASE_PATH']))->createApplication();

        \JieAnthony\LaravelOctaneWorkerman\WebmanConfig::load($worker->app->configPath());
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
