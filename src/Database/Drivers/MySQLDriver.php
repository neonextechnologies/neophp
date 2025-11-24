<?php

namespace NeoPhp\Database\Drivers;

use PDO;
use PDOException;

class MySQLDriver extends DatabaseDriver
{
    public function connect(): void
    {
        try {
            $host = $this->config['host'] ?? '127.0.0.1';
            $port = $this->config['port'] ?? '3306';
            $database = $this->config['database'] ?? '';
            $username = $this->config['username'] ?? 'root';
            $password = $this->config['password'] ?? '';
            $charset = $this->config['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \Exception("MySQL connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new \Exception("MySQL query failed: " . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \Exception("MySQL execute failed: " . $e->getMessage());
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
