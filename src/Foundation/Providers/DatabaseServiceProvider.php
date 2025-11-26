<?php

namespace NeoPhp\Foundation\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Contracts\DatabaseInterface;
use NeoPhp\Database\Database;

/**
 * Database Service Provider
 * Example of how to create service providers
 */
class DatabaseServiceProvider extends ServiceProvider
{
    protected array $provides = [
        DatabaseInterface::class,
        'db',
        Database::class
    ];

    public function register(): void
    {
        $this->singleton(DatabaseInterface::class, function ($app) {
            $config = require $app->basePath('config/database.php');
            return new Database($config);
        });

        $this->alias(DatabaseInterface::class, 'db');
        $this->alias(DatabaseInterface::class, Database::class);
    }

    public function boot(): void
    {
        // Connect to database on boot
        $db = $this->app->make(DatabaseInterface::class);
        $db->connect();
    }
}
