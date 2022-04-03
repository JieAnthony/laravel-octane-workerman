<?php

namespace JieAnthony\LaravelOctaneWorkerman;

class WebmanPlugin
{
    public static function install($event)
    {
        static::findHepler();

        $operation = $event->getOperation();
        
        $autoload = method_exists($operation, 'getPackage') ? $operation->getPackage()->getAutoload() : $operation->getTargetPackage()->getAutoload();
        
        if (!isset($autoload['psr-4'])) {
            return;
        }
        
        $namespace = key($autoload['psr-4']);
        
        $install_function = "\\{$namespace}Install::install";
        $plugin_const = "\\{$namespace}Install::WEBMAN_PLUGIN";
        
        if (defined($plugin_const) && is_callable($install_function)) {
            try {
                $install_function();
            } catch (\Throwable $e) {
                echo $e->getMessage()."\n";
            }
        }
    }

    public static function update($event)
    {
        static::install($event);
    }

    public static function uninstall($event)
    {
        static::findHepler();
        
        $autoload = $event->getOperation()->getPackage()->getAutoload();

        if (!isset($autoload['psr-4'])) {
            return;
        }
        
        $namespace = key($autoload['psr-4']);
        
        $uninstall_function = "\\{$namespace}Install::uninstall";
        $plugin_const = "\\{$namespace}Install::WEBMAN_PLUGIN";
        
        if (defined($plugin_const) && is_callable($uninstall_function)) {
            try {
                $uninstall_function();
            } catch (\Throwable $e) {
                echo $e->getMessage()."\n";
            }
        }
    }

    protected static function findHepler()
    {// Plugin.php in webman
        $file = __DIR__ . '/helpers.php';
        if (is_file($file)) {
            require_once $file;
        }

        // Plugin.php in webman
        $file = base_path() . '/vendor/autoload.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}