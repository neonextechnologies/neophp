<?php

namespace NeoPhp\Foundation;

use NeoPhp\Contracts\ServiceProviderInterface;
use NeoPhp\DI\Container;

/**
 * Service Provider Base Class
 * Inspired by Laravel & Neonex Core module system
 */
abstract class ServiceProvider implements ServiceProviderInterface
{
    protected Container $app;
    protected bool $defer = false;
    protected array $dependencies = [];
    protected array $provides = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Register bindings in the container
     */
    abstract public function register(): void;

    /**
     * Bootstrap services after all providers registered
     */
    public function boot(): void
    {
        // Override in child classes if needed
    }

    /**
     * Get provider dependencies
     */
    public function dependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Check if provider should be deferred
     */
    public function isDeferred(): bool
    {
        return $this->defer;
    }

    /**
     * Get services provided by this provider
     */
    public function provides(): array
    {
        return $this->provides;
    }

    /**
     * Helper: Bind singleton in container
     */
    protected function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->app->singleton($abstract, $concrete);
    }

    /**
     * Helper: Bind instance in container
     */
    protected function bind(string $abstract, mixed $concrete = null): void
    {
        $this->app->bind($abstract, $concrete);
    }

    /**
     * Helper: Bind existing instance
     */
    protected function instance(string $abstract, mixed $instance): void
    {
        $this->app->instance($abstract, $instance);
    }

    /**
     * Helper: Register alias
     */
    protected function alias(string $abstract, string $alias): void
    {
        $this->app->alias($abstract, $alias);
    }
}
