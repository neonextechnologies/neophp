<?php

namespace NeoPhp\Database\Drivers;

use PDO;
use PDOException;

class SQLiteDriver extends DatabaseDriver
{
    public function connect(): void
    {
        try {
            $database = $this->config['database'] ?? ':memory:';
            
            $dsn = "sqlite:{$database}";

            $this->connection = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Enable foreign keys
            $this->connection->exec('PRAGMA foreign_keys = ON');
        } catch (PDOException $e) {
            throw new \Exception("SQLite connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new \Exception("SQLite query failed: " . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \Exception("SQLite execute failed: " . $e->getMessage());
        }
    }

    public function lastInsertId(): int
    {
        return (int) $this->connection->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }
}
