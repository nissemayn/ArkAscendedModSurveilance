<?php

namespace App;

class ConfigController
{
    public static function get(): array
    {
        $configFile = __DIR__ . "/../config.json";
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
        } else {
            echo "Config file not found. Exiting...";
            die();
        }
        return $config;
    }

    public static function set($config)
    {
        // Set config file without adding escape slashes
        $configFile = __DIR__ . "/../config.json";
        file_put_contents($configFile, json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}
