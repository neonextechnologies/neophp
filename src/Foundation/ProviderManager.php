<?php

namespace NeoPhp\Foundation;

use NeoPhp\DI\Container;
use NeoPhp\Contracts\ServiceProviderInterface;

/**
 * Provider Manager
 * Manages service provider lifecycle - discovery, registration, booting
 * Inspired by Neonex Core module manager
 */
class ProviderManager
{
    protected Container $app;
    protected array $providers = [];
    protected array $registered = [];
    protected array $booted = [];
    protected array $deferred = [];
    protected array $providerMap = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Register a service provider
     */
    public function register(string $providerClass): void
    {
        if (isset($this->registered[$providerClass])) {
            return;
        }

        $provider = new $providerClass($this->app);

        if (!$provider instanceof ServiceProviderInterface) {
            throw new \InvalidArgumentException("Provider must implement ServiceProviderInterface");
        }

        // Check if provider is deferred
        if ($provider->isDeferred()) {
            $this->deferProvider($provider, $providerClass);
            return;
        }

        // Check dependencies
        $this->checkDependencies($provider);

        // Register provider
        $provider->register();
        $this->providers[$providerClass] = $provider;
        $this->registered[$providerClass] = true;

        // Map provided services
        foreach ($provider->provides() as $service) {
            $this->providerMap[$service] = $providerClass;
        }
    }

    /**
     * Register multiple providers
     */
    public function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Boot all registered providers
     */
    public function bootProviders(): void
    {
        foreach ($this->providers as $class => $provider) {
            if (!isset($this->booted[$class])) {
                $provider->boot();
                $this->booted[$class] = true;
            }
        }
    }

    /**
     * Boot a specific provider
     */
    public function bootProvider(string $providerClass): void
    {
        if (isset($this->booted[$providerClass])) {
            return;
        }

        if (!isset($this->providers[$providerClass])) {
            throw new \RuntimeException("Provider {$providerClass} not registered");
        }

        $this->providers[$providerClass]->boot();
        $this->booted[$providerClass] = true;
    }

    /**
     * Check if a provider is registered
     */
    public function isRegistered(string $providerClass): bool
    {
        return isset($this->registered[$providerClass]);
    }

    /**
     * Check if a provider is booted
     */
    public function isBooted(string $providerClass): bool
    {
        return isset($this->booted[$providerClass]);
    }

    /**
     * Get provider for a service
     */
    public function getProviderFor(string $service): ?string
    {
        return $this->providerMap[$service] ?? null;
    }

    /**
     * Load deferred provider when service is requested
     */
    public function loadDeferredProvider(string $service): void
    {
        if (!isset($this->providerMap[$service])) {
            return;
        }

        $providerClass = $this->providerMap[$service];

        if (isset($this->deferred[$providerClass])) {
            $provider = $this->deferred[$providerClass];
            $provider->register();
            $this->providers[$providerClass] = $provider;
            $this->registered[$providerClass] = true;
            unset($this->deferred[$providerClass]);

            // Boot immediately if other providers already booted
            if (!empty($this->booted)) {
                $provider->boot();
                $this->booted[$providerClass] = true;
            }
        }
    }

    /**
     * Defer provider registration
     */
    protected function deferProvider(ServiceProviderInterface $provider, string $providerClass): void
    {
        $this->deferred[$providerClass] = $provider;

        foreach ($provider->provides() as $service) {
            $this->providerMap[$service] = $providerClass;
        }
    }

    /**
     * Check provider dependencies
     */
    protected function checkDependencies(ServiceProviderInterface $provider): void
    {
        foreach ($provider->dependencies() as $dependency) {
            if (!$this->isRegistered($dependency)) {
                throw new \RuntimeException(
                    "Provider " . get_class($provider) . " requires {$dependency} to be registered first"
                );
            }
        }
    }

    /**
     * Auto-discover providers from directory
     */
    public function discover(string $directory): array
    {
        $providers = [];

        if (!is_dir($directory)) {
            return $providers;
        }

        $files = glob($directory . '/*Provider.php');

        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);
            if ($className && class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                if (!$reflection->isAbstract() && $reflection->implementsInterface(ServiceProviderInterface::class)) {
                    $providers[] = $className;
                }
            }
        }

        return $providers;
    }

    /**
     * Get class name from file path
     */
    protected function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);

        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch) &&
            preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return $namespaceMatch[1] . '\\' . $classMatch[1];
        }

        return null;
    }

    /**
     * Get all registered providers
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Get all deferred providers
     */
    public function getDeferredProviders(): array
    {
        return $this->deferred;
    }
}
