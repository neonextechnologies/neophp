<?php

namespace NeoPhp\Cache;

class Cache
{
    protected $driver;
    protected $config;

    public function __construct($config)
    {
        if (is_string($config)) {
            // Backward compatibility: string = file path
            $this->config = ['driver' => 'file', 'path' => $config];
        } else {
            $this->config = $config;
        }
        
        $this->initializeDriver();
    }

    protected function initializeDriver(): void
    {
        $driverType = $this->config['driver'] ?? 'file';

        if ($driverType === 'redis') {
            $this->driver = new RedisCache($this->config);
        } else {
            $path = $this->config['path'] ?? 'storage/cache';
            $this->driver = new FileCache($path);
        }
    }

    public function get(string $key, $default = null)
    {
        $value = $this->driver->get($key);
        return $value ?? $default;
    }

    public function put(string $key, $value, int $seconds = 3600): bool
    {
        return $this->driver->put($key, $value, $seconds);
    }

    public function forever(string $key, $value): bool
    {
        return $this->driver->forever($key, $value);
    }

    public function forget(string $key): bool
    {
        return $this->driver->forget($key);
    }

    public function flush(): bool
    {
        return $this->driver->flush();
    }

    public function has(string $key): bool
    {
        return $this->driver->has($key);
    }

    public function remember(string $key, int $seconds, callable $callback)
    {
        return $this->driver->remember($key, $seconds, $callback);
    }

    public function getDriver()
    {
        return $this->driver;
    }
}

// FileCache class
class FileCache
{
    protected $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key, $default = null)
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires_at'] !== null && time() > $data['expires_at']) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    public function put(string $key, $value, int $ttl = 3600): void
    {
        $file = $this->getFilePath($key);

        $data = [
            'value' => $value,
            'expires_at' => $ttl > 0 ? time() + $ttl : null,
        ];

        file_put_contents($file, serialize($data));
    }

    public function forever(string $key, $value): void
    {
        $this->put($key, $value, 0);
    }

    public function forget(string $key): bool
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    public function flush(): void
    {
        $files = glob($this->cachePath . '/*.cache');

        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function remember(string $key, int $ttl, callable $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    protected function getFilePath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}
