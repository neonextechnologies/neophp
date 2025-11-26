<?php

namespace NeoPhp\Contracts;

/**
 * Service Provider Interface
 * Pure contract for service providers (like Neonex Core modules)
 */
interface ServiceProviderInterface
{
    /**
     * Register bindings in the container
     */
    public function register(): void;

    /**
     * Bootstrap services after all providers registered
     */
    public function boot(): void;

    /**
     * Get provider dependencies
     */
    public function dependencies(): array;

    /**
     * Check if provider should be deferred
     */
    public function isDeferred(): bool;

    /**
     * Get services provided by this provider
     */
    public function provides(): array;
}
