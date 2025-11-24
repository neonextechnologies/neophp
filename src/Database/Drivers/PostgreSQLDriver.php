<?php

namespace NeoPhp\Database\Drivers;

use PDO;
use PDOException;

class PostgreSQLDriver extends DatabaseDriver
{
    public function connect(): void
    {
        try {
            $host = $this->config['host'] ?? '127.0.0.1';
            $port = $this->config['port'] ?? '5432';
            $database = $this->config['database'] ?? '';
            $username = $this->config['username'] ?? 'postgres';
            $password = $this->config['password'] ?? '';

            $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \Exception("PostgreSQL connection failed: " . $e->getMessage());
        }
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new \Exception("PostgreSQL query failed: " . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \Exception("PostgreSQL execute failed: " . $e->getMessage());
        }
    }

    public function lastInsertId(): int
    {
        $result = $this->query("SELECT lastval() as id");
        return (int) ($result[0]['id'] ?? 0);
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
