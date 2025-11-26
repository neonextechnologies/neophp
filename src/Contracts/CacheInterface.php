<?php

namespace NeoPhp\Contracts;

/**
 * Cache Driver Interface
 * Pure contract for cache operations
 */
interface CacheInterface
{
    /**
     * Get item from cache
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store item in cache
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Check if item exists in cache
     */
    public function has(string $key): bool;

    /**
     * Delete item from cache
     */
    public function delete(string $key): bool;

    /**
     * Clear all cache
     */
    public function clear(): bool;

    /**
     * Get multiple items from cache
     */
    public function getMultiple(array $keys, mixed $default = null): array;

    /**
     * Store multiple items in cache
     */
    public function setMultiple(array $values, int $ttl = 3600): bool;

    /**
     * Delete multiple items from cache
     */
    public function deleteMultiple(array $keys): bool;

    /**
     * Remember: Get from cache or execute callback and store
     */
    public function remember(string $key, int $ttl, callable $callback): mixed;

    /**
     * Get driver name
     */
    public function getDriverName(): string;
}
