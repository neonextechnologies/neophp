<?php

namespace NeoPhp\Performance;

class Benchmark
{
    protected static $timers = [];
    protected static $memory = [];

    public static function start(string $name): void
    {
        static::$timers[$name] = microtime(true);
        static::$memory[$name] = memory_get_usage();
    }

    public static function end(string $name): array
    {
        if (!isset(static::$timers[$name])) {
            return ['time' => 0, 'memory' => 0];
        }

        $time = microtime(true) - static::$timers[$name];
        $memory = memory_get_usage() - static::$memory[$name];

        unset(static::$timers[$name], static::$memory[$name]);

        return [
            'time' => round($time * 1000, 2), // ms
            'memory' => round($memory / 1024, 2), // KB
        ];
    }

    public static function measure(string $name, callable $callback)
    {
        static::start($name);
        $result = $callback();
        $stats = static::end($name);

        return [
            'result' => $result,
            'stats' => $stats,
        ];
    }

    public static function getMemoryUsage(): float
    {
        return round(memory_get_usage() / 1024 / 1024, 2); // MB
    }

    public static function getPeakMemoryUsage(): float
    {
        return round(memory_get_peak_usage() / 1024 / 1024, 2); // MB
    }
}
