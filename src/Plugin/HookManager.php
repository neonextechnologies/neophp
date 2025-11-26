<?php

namespace NeoPhp\Plugin;

/**
 * Hook Manager
 * WordPress-style action/filter hooks system
 * Inspired by Neonex Core event hooks
 */
class HookManager
{
    protected static array $actions = [];
    protected static array $filters = [];
    protected static array $current = [];

    /**
     * Register an action hook
     */
    public static function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        self::$actions[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs
        ];
    }

    /**
     * Register a filter hook
     */
    public static function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        self::$filters[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs
        ];
    }

    /**
     * Execute action hooks
     */
    public static function doAction(string $hook, ...$args): void
    {
        if (!isset(self::$actions[$hook])) {
            return;
        }

        self::$current[] = $hook;

        $hooks = self::$actions[$hook];
        ksort($hooks);

        foreach ($hooks as $priority => $callbacks) {
            foreach ($callbacks as $hookData) {
                $callback = $hookData['callback'];
                $acceptedArgs = $hookData['accepted_args'];
                
                $callbackArgs = array_slice($args, 0, $acceptedArgs);
                call_user_func_array($callback, $callbackArgs);
            }
        }

        array_pop(self::$current);
    }

    /**
     * Apply filter hooks
     */
    public static function applyFilters(string $hook, mixed $value, ...$args): mixed
    {
        if (!isset(self::$filters[$hook])) {
            return $value;
        }

        self::$current[] = $hook;

        $hooks = self::$filters[$hook];
        ksort($hooks);

        array_unshift($args, $value);

        foreach ($hooks as $priority => $callbacks) {
            foreach ($callbacks as $hookData) {
                $callback = $hookData['callback'];
                $acceptedArgs = $hookData['accepted_args'];
                
                $callbackArgs = array_slice($args, 0, $acceptedArgs);
                $value = call_user_func_array($callback, $callbackArgs);
                $args[0] = $value;
            }
        }

        array_pop(self::$current);

        return $value;
    }

    /**
     * Remove an action hook
     */
    public static function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset(self::$actions[$hook][$priority])) {
            return false;
        }

        foreach (self::$actions[$hook][$priority] as $key => $hookData) {
            if ($hookData['callback'] === $callback) {
                unset(self::$actions[$hook][$priority][$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Remove a filter hook
     */
    public static function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset(self::$filters[$hook][$priority])) {
            return false;
        }

        foreach (self::$filters[$hook][$priority] as $key => $hookData) {
            if ($hookData['callback'] === $callback) {
                unset(self::$filters[$hook][$priority][$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Remove all hooks for a specific hook name
     */
    public static function removeAllActions(string $hook, ?int $priority = null): bool
    {
        if ($priority !== null) {
            if (isset(self::$actions[$hook][$priority])) {
                unset(self::$actions[$hook][$priority]);
                return true;
            }
            return false;
        }

        if (isset(self::$actions[$hook])) {
            unset(self::$actions[$hook]);
            return true;
        }

        return false;
    }

    /**
     * Remove all filters for a specific hook name
     */
    public static function removeAllFilters(string $hook, ?int $priority = null): bool
    {
        if ($priority !== null) {
            if (isset(self::$filters[$hook][$priority])) {
                unset(self::$filters[$hook][$priority]);
                return true;
            }
            return false;
        }

        if (isset(self::$filters[$hook])) {
            unset(self::$filters[$hook]);
            return true;
        }

        return false;
    }

    /**
     * Check if action has been registered
     */
    public static function hasAction(string $hook, ?callable $callback = null): bool
    {
        if (!isset(self::$actions[$hook])) {
            return false;
        }

        if ($callback === null) {
            return true;
        }

        foreach (self::$actions[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $hookData) {
                if ($hookData['callback'] === $callback) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if filter has been registered
     */
    public static function hasFilter(string $hook, ?callable $callback = null): bool
    {
        if (!isset(self::$filters[$hook])) {
            return false;
        }

        if ($callback === null) {
            return true;
        }

        foreach (self::$filters[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $hookData) {
                if ($hookData['callback'] === $callback) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get current hook being executed
     */
    public static function currentHook(): ?string
    {
        return end(self::$current) ?: null;
    }

    /**
     * Check if currently executing a hook
     */
    public static function doingHook(?string $hook = null): bool
    {
        if ($hook === null) {
            return !empty(self::$current);
        }

        return in_array($hook, self::$current);
    }

    /**
     * Get all registered actions
     */
    public static function getActions(): array
    {
        return self::$actions;
    }

    /**
     * Get all registered filters
     */
    public static function getFilters(): array
    {
        return self::$filters;
    }
}
