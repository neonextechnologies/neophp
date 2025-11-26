<?php

namespace NeoPhp\Foundation\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Contracts\CacheInterface;
use NeoPhp\Cache\Cache;

/**
 * Cache Service Provider
 */
class CacheServiceProvider extends ServiceProvider
{
    protected array $provides = [
        CacheInterface::class,
        'cache',
        Cache::class
    ];

    protected bool $defer = true; // Deferred loading

    public function register(): void
    {
        $this->singleton(CacheInterface::class, function ($app) {
            $config = require $app->basePath('config/cache.php');
            return new Cache($config);
        });

        $this->alias(CacheInterface::class, 'cache');
        $this->alias(CacheInterface::class, Cache::class);
    }
}
