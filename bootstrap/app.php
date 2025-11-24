<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \NeoPhp\Core\Application(
    dirname(__DIR__)
);

// Register Database Service
$app->singleton(\NeoPhp\Database\Database::class, function ($app) {
    $config = $app->make('config')->get('database.connections.mysql');
    return new \NeoPhp\Database\Database($config);
});

$app->alias(\NeoPhp\Database\Database::class, 'db');

// Register Module Loader
$app->singleton(\NeoPhp\Core\ModuleLoader::class, function ($app) {
    return new \NeoPhp\Core\ModuleLoader($app);
});

$app->alias(\NeoPhp\Core\ModuleLoader::class, 'moduleLoader');

// Register View Service
$app->singleton(\NeoPhp\View\View::class, function ($app) {
    return new \NeoPhp\View\View($app->basePath('resources/views'));
});

$app->alias(\NeoPhp\View\View::class, 'view');

// Register Auth Service
$app->singleton(\NeoPhp\Auth\Auth::class, function ($app) {
    return new \NeoPhp\Auth\Auth($app->make('db'));
});

$app->alias(\NeoPhp\Auth\Auth::class, 'auth');

// Register Cache Service
$app->singleton(\NeoPhp\Cache\Cache::class, function ($app) {
    $config = $app->make('config');
    $cacheConfig = $config->get('cache');
    $defaultDriver = $cacheConfig['default'] ?? 'file';
    $driverConfig = $cacheConfig['drivers'][$defaultDriver] ?? ['driver' => 'file', 'path' => $app->storagePath('cache')];
    
    return new \NeoPhp\Cache\Cache($driverConfig);
});

$app->alias(\NeoPhp\Cache\Cache::class, 'cache');

// Register Session Service
$app->singleton(\NeoPhp\Session\Session::class, function ($app) {
    return new \NeoPhp\Session\Session();
});

$app->alias(\NeoPhp\Session\Session::class, 'session');

// Register Logger Service
$app->singleton(\NeoPhp\Logging\Logger::class, function ($app) {
    return new \NeoPhp\Logging\Logger($app->storagePath('logs'));
});

$app->alias(\NeoPhp\Logging\Logger::class, 'log');

// Register Storage Service
$app->singleton(\NeoPhp\Storage\Storage::class, function ($app) {
    return new \NeoPhp\Storage\Storage($app->storagePath('app'));
});

$app->alias(\NeoPhp\Storage\Storage::class, 'storage');

// Register Queue Service
$app->singleton(\NeoPhp\Queue\Queue::class, function ($app) {
    $driver = new \NeoPhp\Queue\DatabaseQueueDriver($app->make('db'));
    return new \NeoPhp\Queue\Queue($driver);
});

$app->alias(\NeoPhp\Queue\Queue::class, 'queue');

// Register Mailer Service
$app->singleton(\NeoPhp\Mail\Mailer::class, function ($app) {
    $config = $app->make('config')->get('mail') ?? [];
    return new \NeoPhp\Mail\Mailer($config);
});

$app->alias(\NeoPhp\Mail\Mailer::class, 'mail');

return $app;
