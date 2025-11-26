<?php

// Helper functions for Foundation Framework features

if (!function_exists('metadata')) {
    /**
     * Get metadata repository instance
     */
    function metadata(?string $model = null): mixed
    {
        $repo = app(\NeoPhp\Metadata\MetadataRepository::class);
        
        if ($model === null) {
            return $repo;
        }
        
        return $repo->getModelMetadata($model);
    }
}

if (!function_exists('form')) {
    /**
     * Get form builder instance
     */
    function form(): \NeoPhp\Forms\FormBuilder
    {
        return app(\NeoPhp\Forms\FormBuilder::class);
    }
}

if (!function_exists('plugin')) {
    /**
     * Get plugin manager instance
     */
    function plugin(?string $name = null): mixed
    {
        $manager = app(\NeoPhp\Plugin\PluginManager::class);
        
        if ($name === null) {
            return $manager;
        }
        
        return $manager->getPlugin($name);
    }
}

if (!function_exists('provider')) {
    /**
     * Get provider manager instance
     */
    function provider(): \NeoPhp\Foundation\ProviderManager
    {
        return app(\NeoPhp\Foundation\ProviderManager::class);
    }
}

if (!function_exists('hook_action')) {
    /**
     * Add action hook
     */
    function hook_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        \NeoPhp\Plugin\HookManager::addAction($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('hook_filter')) {
    /**
     * Add filter hook
     */
    function hook_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        \NeoPhp\Plugin\HookManager::addFilter($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('do_action')) {
    /**
     * Execute action hooks
     */
    function do_action(string $hook, ...$args): void
    {
        \NeoPhp\Plugin\HookManager::doAction($hook, ...$args);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Apply filter hooks
     */
    function apply_filters(string $hook, mixed $value, ...$args): mixed
    {
        return \NeoPhp\Plugin\HookManager::applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('has_action')) {
    /**
     * Check if action hook exists
     */
    function has_action(string $hook, ?callable $callback = null): bool
    {
        return \NeoPhp\Plugin\HookManager::hasAction($hook, $callback);
    }
}

if (!function_exists('has_filter')) {
    /**
     * Check if filter hook exists
     */
    function has_filter(string $hook, ?callable $callback = null): bool
    {
        return \NeoPhp\Plugin\HookManager::hasFilter($hook, $callback);
    }
}

if (!function_exists('current_hook')) {
    /**
     * Get current executing hook
     */
    function current_hook(): ?string
    {
        return \NeoPhp\Plugin\HookManager::currentHook();
    }
}
