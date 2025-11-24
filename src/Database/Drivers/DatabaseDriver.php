<?php

namespace NeoPhp\Database\Drivers;

use PDO;

abstract class DatabaseDriver
{
    protected $connection;
    protected $config;

    abstract public function connect(): void;
    abstract public function query(string $sql, array $params = []): array;
    abstract public function execute(string $sql, array $params = []): bool;
    abstract public function lastInsertId(): int;
    abstract public function beginTransaction(): bool;
    abstract public function commit(): bool;
    abstract public function rollBack(): bool;
    abstract public function disconnect(): void;

    public function getConnection()
    {
        return $this->connection;
    }
}
