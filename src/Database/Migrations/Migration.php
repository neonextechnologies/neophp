<?php

namespace NeoPhp\Database\Migrations;

/**
 * Base Migration Class
 * All migrations extend this class
 */
abstract class Migration
{
    /**
     * Run the migrations
     */
    abstract public function up(): void;

    /**
     * Reverse the migrations
     */
    abstract public function down(): void;

    /**
     * Get migration name
     */
    public function getName(): string
    {
        return static::class;
    }
}
