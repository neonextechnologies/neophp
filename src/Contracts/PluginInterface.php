<?php

namespace NeoPhp\Contracts;

/**
 * Plugin Interface
 * Pure contract for plugins (Neonex Core style)
 */
interface PluginInterface
{
    /**
     * Install plugin
     */
    public function install(): void;

    /**
     * Uninstall plugin
     */
    public function uninstall(): void;

    /**
     * Boot plugin
     */
    public function boot(): void;

    /**
     * Get plugin name
     */
    public function getName(): string;

    /**
     * Get plugin version
     */
    public function getVersion(): string;

    /**
     * Get plugin description
     */
    public function getDescription(): string;

    /**
     * Get plugin dependencies
     */
    public function getDependencies(): array;

    /**
     * Check if plugin is active
     */
    public function isActive(): bool;

    /**
     * Get plugin hooks
     */
    public function getHooks(): array;
}
