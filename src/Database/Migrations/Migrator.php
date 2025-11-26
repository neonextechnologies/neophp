<?php

namespace NeoPhp\Database\Migrations;

use NeoPhp\Contracts\DatabaseInterface;

/**
 * Migration Manager (Migrator)
 * Handles running and rolling back migrations
 */
class Migrator
{
    protected DatabaseInterface $db;
    protected string $migrationsPath;
    protected string $table = 'migrations';

    public function __construct(DatabaseInterface $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->migrationsPath = $migrationsPath;
        $this->ensureMigrationsTable();
    }

    /**
     * Ensure migrations table exists
     */
    protected function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);
    }

    /**
     * Run pending migrations
     */
    public function run(): array
    {
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            return [];
        }

        $batch = $this->getNextBatch();
        $executed = [];

        foreach ($migrations as $migration) {
            $this->runMigration($migration, $batch);
            $executed[] = $migration;
        }

        return $executed;
    }

    /**
     * Rollback last batch
     */
    public function rollback(int $steps = 1): array
    {
        $batches = $this->getExecutedBatches();
        $rolledBack = [];

        for ($i = 0; $i < $steps; $i++) {
            if (empty($batches)) {
                break;
            }

            $batch = array_shift($batches);
            $migrations = $this->getMigrationsInBatch($batch);

            foreach (array_reverse($migrations) as $migration) {
                $this->rollbackMigration($migration);
                $rolledBack[] = $migration;
            }
        }

        return $rolledBack;
    }

    /**
     * Reset all migrations
     */
    public function reset(): array
    {
        $migrations = $this->getExecutedMigrations();
        $rolledBack = [];

        foreach (array_reverse($migrations) as $migration) {
            $this->rollbackMigration($migration);
            $rolledBack[] = $migration;
        }

        return $rolledBack;
    }

    /**
     * Refresh migrations (rollback all and re-run)
     */
    public function refresh(): array
    {
        $this->reset();
        return $this->run();
    }

    /**
     * Fresh migrations (drop all tables and re-run)
     */
    public function fresh(): array
    {
        $this->dropAllTables();
        $this->ensureMigrationsTable();
        return $this->run();
    }

    /**
     * Get migration status
     */
    public function status(): array
    {
        $executed = $this->getExecutedMigrations();
        $available = $this->getAvailableMigrations();
        
        $status = [];

        foreach ($available as $migration) {
            $status[] = [
                'migration' => $migration,
                'executed' => in_array($migration, $executed),
                'batch' => $this->getMigrationBatch($migration),
            ];
        }

        return $status;
    }

    /**
     * Run a single migration
     */
    protected function runMigration(string $migration, int $batch): void
    {
        $instance = $this->resolveMigration($migration);
        
        // Run up method
        $instance->up();

        // Record migration
        $this->db->query(
            "INSERT INTO `{$this->table}` (`migration`, `batch`) VALUES (?, ?)",
            [$migration, $batch]
        );
    }

    /**
     * Rollback a single migration
     */
    protected function rollbackMigration(string $migration): void
    {
        $instance = $this->resolveMigration($migration);
        
        // Run down method
        $instance->down();

        // Remove migration record
        $this->db->query(
            "DELETE FROM `{$this->table}` WHERE `migration` = ?",
            [$migration]
        );
    }

    /**
     * Resolve migration instance
     */
    protected function resolveMigration(string $migration): Migration
    {
        $file = $this->migrationsPath . '/' . $migration . '.php';
        
        if (!file_exists($file)) {
            throw new \RuntimeException("Migration file not found: {$file}");
        }

        $instance = require $file;

        if (!$instance instanceof Migration) {
            throw new \RuntimeException("Migration must return a Migration instance: {$migration}");
        }

        return $instance;
    }

    /**
     * Get pending migrations
     */
    protected function getPendingMigrations(): array
    {
        $executed = $this->getExecutedMigrations();
        $available = $this->getAvailableMigrations();

        return array_diff($available, $executed);
    }

    /**
     * Get executed migrations
     */
    protected function getExecutedMigrations(): array
    {
        $result = $this->db->query("SELECT `migration` FROM `{$this->table}` ORDER BY `id`");
        return array_column($result, 'migration');
    }

    /**
     * Get available migrations
     */
    protected function getAvailableMigrations(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get next batch number
     */
    protected function getNextBatch(): int
    {
        $result = $this->db->query("SELECT MAX(`batch`) as max_batch FROM `{$this->table}`");
        $maxBatch = $result[0]['max_batch'] ?? 0;
        return $maxBatch + 1;
    }

    /**
     * Get executed batches
     */
    protected function getExecutedBatches(): array
    {
        $result = $this->db->query("SELECT DISTINCT `batch` FROM `{$this->table}` ORDER BY `batch` DESC");
        return array_column($result, 'batch');
    }

    /**
     * Get migrations in batch
     */
    protected function getMigrationsInBatch(int $batch): array
    {
        $result = $this->db->query(
            "SELECT `migration` FROM `{$this->table}` WHERE `batch` = ? ORDER BY `id`",
            [$batch]
        );
        return array_column($result, 'migration');
    }

    /**
     * Get migration batch number
     */
    protected function getMigrationBatch(string $migration): ?int
    {
        $result = $this->db->query(
            "SELECT `batch` FROM `{$this->table}` WHERE `migration` = ?",
            [$migration]
        );
        return $result[0]['batch'] ?? null;
    }

    /**
     * Drop all tables
     */
    protected function dropAllTables(): void
    {
        // Disable foreign key checks
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        // Get all tables
        $tables = $this->db->query('SHOW TABLES');
        
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $this->db->query("DROP TABLE IF EXISTS `{$tableName}`");
        }

        // Re-enable foreign key checks
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}
