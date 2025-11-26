<?php

namespace NeoPhp\Database\Schema;

use NeoPhp\Contracts\DatabaseInterface;

/**
 * Schema Builder
 * Provides static methods for schema operations
 */
class Schema
{
    protected static ?DatabaseInterface $db = null;

    /**
     * Set database connection
     */
    public static function setConnection(DatabaseInterface $db): void
    {
        self::$db = $db;
    }

    /**
     * Get database connection
     */
    protected static function getConnection(): DatabaseInterface
    {
        if (!self::$db) {
            self::$db = app('db');
        }

        return self::$db;
    }

    /**
     * Create a new table
     */
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $blueprint->create();
        
        $callback($blueprint);
        
        $sql = $blueprint->toSql();
        self::getConnection()->query($sql);
    }

    /**
     * Modify an existing table
     */
    public static function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        
        $callback($blueprint);
        
        $statements = $blueprint->toStatements();
        
        foreach ($statements as $sql) {
            self::getConnection()->query($sql);
        }
    }

    /**
     * Drop a table
     */
    public static function drop(string $table): void
    {
        $sql = "DROP TABLE `{$table}`";
        self::getConnection()->query($sql);
    }

    /**
     * Drop a table if it exists
     */
    public static function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS `{$table}`";
        self::getConnection()->query($sql);
    }

    /**
     * Rename a table
     */
    public static function rename(string $from, string $to): void
    {
        $sql = "RENAME TABLE `{$from}` TO `{$to}`";
        self::getConnection()->query($sql);
    }

    /**
     * Check if table exists
     */
    public static function hasTable(string $table): bool
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = self::getConnection()->query($sql);
        
        return count($result) > 0;
    }

    /**
     * Check if column exists
     */
    public static function hasColumn(string $table, string $column): bool
    {
        $sql = "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'";
        $result = self::getConnection()->query($sql);
        
        return count($result) > 0;
    }

    /**
     * Get column listing
     */
    public static function getColumnListing(string $table): array
    {
        $sql = "SHOW COLUMNS FROM `{$table}`";
        $columns = self::getConnection()->query($sql);
        
        return array_column($columns, 'Field');
    }
}
