<?php

namespace NeoPhp\Plugin;

use NeoPhp\Contracts\PluginInterface;

/**
 * Plugin Base Class
 * Inspired by Neonex Core plugin system
 */
abstract class Plugin implements PluginInterface
{
    protected string $name;
    protected string $version = '1.0.0';
    protected string $description = '';
    protected array $dependencies = [];
    protected bool $active = false;
    protected array $hooks = [];

    /**
     * Install plugin
     */
    public function install(): void
    {
        // Override in child classes
    }

    /**
     * Uninstall plugin
     */
    public function uninstall(): void
    {
        // Override in child classes
    }

    /**
     * Boot plugin
     */
    abstract public function boot(): void;

    /**
     * Get plugin name
     */
    public function getName(): string
    {
        return $this->name ?? static::class;
    }

    /**
     * Get plugin version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get plugin description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get plugin dependencies
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Check if plugin is active
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Activate plugin
     */
    public function activate(): void
    {
        $this->active = true;
    }

    /**
     * Deactivate plugin
     */
    public function deactivate(): void
    {
        $this->active = false;
    }

    /**
     * Get plugin hooks
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * Register hook
     */
    protected function addHook(string $hook, callable $callback, int $priority = 10): void
    {
        if (!isset($this->hooks[$hook])) {
            $this->hooks[$hook] = [];
        }

        $this->hooks[$hook][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
    }
}
