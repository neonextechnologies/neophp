<?php

namespace NeoPhp\Contracts;

/**
 * Metadata Interface
 * Pure contract for metadata operations
 */
interface MetadataInterface
{
    /**
     * Get table metadata
     */
    public function getTableMetadata(string $table): array;

    /**
     * Get model metadata
     */
    public function getModelMetadata(string $model): array;

    /**
     * Get field metadata
     */
    public function getFieldMetadata(string $table, string $field): array;

    /**
     * Get validation rules from metadata
     */
    public function getValidationRules(string $model): array;

    /**
     * Get relationships from metadata
     */
    public function getRelationships(string $model): array;

    /**
     * Parse metadata from class attributes
     */
    public function parseFromClass(string $class): array;

    /**
     * Cache metadata
     */
    public function cache(string $key, array $metadata): void;

    /**
     * Clear metadata cache
     */
    public function clearCache(?string $key = null): void;
}
