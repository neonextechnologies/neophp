<?php

namespace NeoPhp\Contracts;

/**
 * Storage Driver Interface
 * Pure contract for file storage operations
 */
interface StorageInterface
{
    /**
     * Store a file
     */
    public function put(string $path, string $contents): bool;

    /**
     * Store a file from uploaded file
     */
    public function putFile(string $path, array $file): string;

    /**
     * Get file contents
     */
    public function get(string $path): string;

    /**
     * Check if file exists
     */
    public function exists(string $path): bool;

    /**
     * Delete a file
     */
    public function delete(string $path): bool;

    /**
     * Copy a file
     */
    public function copy(string $from, string $to): bool;

    /**
     * Move a file
     */
    public function move(string $from, string $to): bool;

    /**
     * Get file size
     */
    public function size(string $path): int;

    /**
     * Get file's last modification time
     */
    public function lastModified(string $path): int;

    /**
     * Get all files in directory
     */
    public function files(string $directory = ''): array;

    /**
     * Get all directories
     */
    public function directories(string $directory = ''): array;

    /**
     * Create a directory
     */
    public function makeDirectory(string $path): bool;

    /**
     * Delete a directory
     */
    public function deleteDirectory(string $directory): bool;

    /**
     * Get file URL
     */
    public function url(string $path): string;

    /**
     * Get driver name
     */
    public function getDriverName(): string;
}
