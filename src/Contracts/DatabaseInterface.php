<?php

namespace NeoPhp\Contracts;

/**
 * Database Driver Interface
 * Pure contract for database operations
 */
interface DatabaseInterface
{
    /**
     * Connect to database
     */
    public function connect(): void;

    /**
     * Execute a query and return results
     */
    public function query(string $sql, array $params = []): array;

    /**
     * Execute a statement (INSERT, UPDATE, DELETE)
     */
    public function execute(string $sql, array $params = []): bool;

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool;

    /**
     * Commit transaction
     */
    public function commit(): bool;

    /**
     * Rollback transaction
     */
    public function rollback(): bool;

    /**
     * Get last insert ID
     */
    public function lastInsertId(): int;

    /**
     * Find record by ID
     */
    public function find(string $table, int $id, string $primaryKey = 'id'): ?array;

    /**
     * Get all records from table
     */
    public function all(string $table): array;

    /**
     * Insert data into table
     */
    public function insert(string $table, array $data): int;

    /**
     * Update records in table
     */
    public function update(string $table, array $data, string $where, array $params = []): int;

    /**
     * Delete records from table
     */
    public function delete(string $table, string $where, array $params = []): int;

    /**
     * Get driver name
     */
    public function getDriverName(): string;
}
