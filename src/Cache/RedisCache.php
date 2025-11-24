<?php

namespace NeoPhp\Cache;

use Predis\Client;

class RedisCache
{
    protected $redis;
    protected $prefix;

    public function __construct(array $config = [])
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $password = $config['password'] ?? null;
        $database = $config['database'] ?? 1;
        $this->prefix = $config['prefix'] ?? 'neophp_cache:';

        $params = [
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port,
            'database' => $database,
        ];

        if ($password) {
            $params['password'] = $password;
        }

        $this->redis = new Client($params);
        $this->redis->connect();
    }

    public function get(string $key)
    {
        $value = $this->redis->get($this->prefix . $key);
        
        if ($value === null) {
            return null;
        }

        $data = unserialize($value);
        
        // Check expiration
        if (isset($data['expires_at']) && time() > $data['expires_at']) {
            $this->forget($key);
            return null;
        }

        return $data['value'] ?? null;
    }

    public function put(string $key, $value, int $seconds = 3600): bool
    {
        $data = [
            'value' => $value,
            'expires_at' => time() + $seconds,
        ];

        return (bool) $this->redis->setex(
            $this->prefix . $key,
            $seconds,
            serialize($data)
        );
    }

    public function forever(string $key, $value): bool
    {
        $data = [
            'value' => $value,
            'expires_at' => null,
        ];

        return (bool) $this->redis->set($this->prefix . $key, serialize($data));
    }

    public function forget(string $key): bool
    {
        return (bool) $this->redis->del([$this->prefix . $key]);
    }

    public function flush(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        
        if (empty($keys)) {
            return true;
        }

        return (bool) $this->redis->del($keys);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function remember(string $key, int $seconds, callable $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $seconds);

        return $value;
    }

    public function increment(string $key, int $value = 1): int
    {
        return $this->redis->incrby($this->prefix . $key, $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->redis->decrby($this->prefix . $key, $value);
    }

    public function getRedis(): Client
    {
        return $this->redis;
    }
}
