<?php

namespace NeoPhp\Events;

class EventDispatcher
{
    protected static $listeners = [];

    public static function listen(string $event, callable $listener): void
    {
        if (!isset(static::$listeners[$event])) {
            static::$listeners[$event] = [];
        }

        static::$listeners[$event][] = $listener;
    }

    public static function dispatch(string $event, $payload = null)
    {
        if (!isset(static::$listeners[$event])) {
            return null;
        }

        $responses = [];

        foreach (static::$listeners[$event] as $listener) {
            $responses[] = $listener($payload);
        }

        return $responses;
    }

    public static function forget(string $event): void
    {
        unset(static::$listeners[$event]);
    }

    public static function clearAll(): void
    {
        static::$listeners = [];
    }

    public static function hasListeners(string $event): bool
    {
        return isset(static::$listeners[$event]) && count(static::$listeners[$event]) > 0;
    }
}
