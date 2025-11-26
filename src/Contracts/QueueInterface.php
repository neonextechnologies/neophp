<?php

namespace NeoPhp\Contracts;

/**
 * Queue Driver Interface
 * Pure contract for queue operations
 */
interface QueueInterface
{
    /**
     * Push job to queue
     */
    public function push(string $job, array $data = [], string $queue = 'default'): bool;

    /**
     * Process next job in queue
     */
    public function work(string $queue = 'default'): void;

    /**
     * Get queue size
     */
    public function size(string $queue = 'default'): int;

    /**
     * Clear queue
     */
    public function clear(string $queue = 'default'): bool;

    /**
     * Get failed jobs
     */
    public function failed(): array;

    /**
     * Retry failed job
     */
    public function retry(string $jobId): bool;

    /**
     * Get driver name
     */
    public function getDriverName(): string;
}
