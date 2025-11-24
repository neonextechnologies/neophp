<?php

namespace NeoPhp\Config;

class Config
{
    protected $items = [];
    protected $configPath;

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
        $this->loadConfigFiles();
        $this->loadEnvironmentVariables();
    }

    protected function loadConfigFiles(): void
    {
        if (!is_dir($this->configPath)) {
            return;
        }

        $files = glob($this->configPath . '/*.php');

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    protected function loadEnvironmentVariables(): void
    {
        $envFile = dirname($this->configPath) . '/.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes
            $value = trim($value, '"\'');

            // Convert to proper types
            if ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            } elseif ($value === 'null') {
                $value = null;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->items;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->items;

        while (count($keys) > 1) {
            $segment = array_shift($keys);

            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }

            $config = &$config[$segment];
        }

        $config[array_shift($keys)] = $value;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function all(): array
    {
        return $this->items;
    }
}
