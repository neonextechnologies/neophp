<?php

namespace NeoPhp\Database\Drivers;

use Predis\Client;

class RedisDriver extends DatabaseDriver
{
    protected $prefix = '';

    public function connect(): void
    {
        try {
            $host = $this->config['host'] ?? '127.0.0.1';
            $port = $this->config['port'] ?? 6379;
            $password = $this->config['password'] ?? null;
            $database = $this->config['database'] ?? 0;
            $this->prefix = $this->config['prefix'] ?? 'neophp:';

            $params = [
                'scheme' => 'tcp',
                'host' => $host,
                'port' => $port,
                'database' => $database,
            ];

            if ($password) {
                $params['password'] = $password;
            }

            $this->connection = new Client($params);
            $this->connection->connect();
        } catch (\Exception $e) {
            throw new \Exception("Redis connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): array
    {
        // For Redis, we use key patterns
        $pattern = $this->prefix . $sql;
        $keys = $this->connection->keys($pattern);
        
        $results = [];
        foreach ($keys as $key) {
            $value = $this->connection->get($key);
            $results[] = [
                'key' => str_replace($this->prefix, '', $key),
                'value' => $this->unserialize($value),
            ];
        }

        return $results;
    }

    public function execute(string $sql, array $params = []): bool
    {
        // Not applicable for Redis
        return true;
    }

    public function get(string $key)
    {
        $value = $this->connection->get($this->prefix . $key);
        
        if ($value === null) {
            return null;
        }

        return $this->unserialize($value);
    }

    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $key = $this->prefix . $key;
        $value = $this->serialize($value);

        if ($ttl) {
            return (bool) $this->connection->setex($key, $ttl, $value);
        }

        return (bool) $this->connection->set($key, $value);
    }

    public function delete(string $key): bool
    {
        return (bool) $this->connection->del([$this->prefix . $key]);
    }

    public function exists(string $key): bool
    {
        return (bool) $this->connection->exists($this->prefix . $key);
    }

    public function increment(string $key, int $value = 1): int
    {
        return $this->connection->incrby($this->prefix . $key, $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->connection->decrby($this->prefix . $key, $value);
    }

    public function flush(): bool
    {
        return $this->connection->flushdb();
    }

    public function hSet(string $key, string $field, $value): bool
    {
        return (bool) $this->connection->hset(
            $this->prefix . $key,
            $field,
            $this->serialize($value)
        );
    }

    public function hGet(string $key, string $field)
    {
        $value = $this->connection->hget($this->prefix . $key, $field);
        
        if ($value === null) {
            return null;
        }

        return $this->unserialize($value);
    }

    public function hGetAll(string $key): array
    {
        $values = $this->connection->hgetall($this->prefix . $key);
        
        $result = [];
        foreach ($values as $field => $value) {
            $result[$field] = $this->unserialize($value);
        }

        return $result;
    }

    protected function serialize($value): string
    {
        return serialize($value);
    }

    protected function unserialize(string $value)
    {
        return unserialize($value);
    }

    public function lastInsertId(): int
    {
        return 0;
    }

    public function beginTransaction(): bool
    {
        $this->connection->multi();
        return true;
    }

    public function commit(): bool
    {
        $this->connection->exec();
        return true;
    }

    public function rollBack(): bool
    {
        $this->connection->discard();
        return true;
    }

    public function disconnect(): void
    {
        if ($this->connection) {
            $this->connection->disconnect();
        }
        $this->connection = null;
    }
}
