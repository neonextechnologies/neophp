<?php

namespace NeoPhp\Database;

use NeoPhp\Database\Drivers\DatabaseDriver;
use NeoPhp\Database\Drivers\MySQLDriver;
use NeoPhp\Database\Drivers\PostgreSQLDriver;
use NeoPhp\Database\Drivers\SQLiteDriver;
use NeoPhp\Database\Drivers\SQLServerDriver;
use NeoPhp\Database\Drivers\TursoDriver;
use NeoPhp\Database\Drivers\MongoDBDriver;

class Database
{
    protected static $instance;
    protected $driver;
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    protected function connect(): void
    {
        $driverName = $this->config['driver'] ?? 'mysql';
        
        $this->driver = match(strtolower($driverName)) {
            'mysql' => new MySQLDriver(),
            'pgsql', 'postgresql' => new PostgreSQLDriver(),
            'sqlite' => new SQLiteDriver(),
            'sqlsrv', 'sqlserver' => new SQLServerDriver(),
            'turso', 'libsql' => new TursoDriver(),
            'mongodb', 'mongo' => new MongoDBDriver(),
            default => throw new \Exception("Unsupported database driver: {$driverName}"),
        };

        $this->driver->config = $this->config;
        $this->driver->connect();
    }

    public static function getInstance(array $config = []): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($config);
        }

        return static::$instance;
    }

    public function query(string $sql, array $params = []): array
    {
        return $this->driver->query($sql, $params);
    }

    public function execute(string $sql, array $params = []): bool
    {
        return $this->driver->execute($sql, $params);
    }

    public function insert(string $table, array $data): int
    {
        // For NoSQL databases
        if ($this->driver instanceof MongoDBDriver) {
            return (int) $this->driver->insertOne($table, $data);
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->execute($sql, array_values($data));
        
        return $this->driver->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        $setClause = implode(', ', $sets);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    public function find(string $table, int $id, string $primaryKey = 'id'): ?array
    {
        $sql = "SELECT * FROM {$table} WHERE {$primaryKey} = ? LIMIT 1";
        $results = $this->query($sql, [$id]);
        
        return $results[0] ?? null;
    }

    public function all(string $table): array
    {
        return $this->query("SELECT * FROM {$table}");
    }

    public function beginTransaction(): bool
    {
        return $this->driver->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->driver->commit();
    }

    public function rollBack(): bool
    {
        return $this->driver->rollBack();
    }

    public function getDriver(): DatabaseDriver
    {
        return $this->driver;
    }

    public function getPdo()
    {
        return $this->driver->getConnection();
    }

    public function getDriverName(): string
    {
        return $this->config['driver'] ?? 'mysql';
    }

    public function isNoSQL(): bool
    {
        return $this->driver instanceof MongoDBDriver;
    }
}
