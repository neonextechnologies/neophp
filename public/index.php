<?php

/** @var \NeoPhp\Core\Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Load modules using auto-discovery
$moduleLoader = $app->make('moduleLoader');

// Option 1: Auto-discover modules from directory
$moduleLoader->loadModulesFromDirectory(
    $app->basePath('app/Modules'),
    'App\\Modules'
);

// Option 2: Or manually load root module (which imports others)
// $moduleLoader->loadModule(\App\AppModule::class);

// Load traditional routes (optional - for backward compatibility)
if (file_exists(__DIR__ . '/../routes/web.php')) {
    require_once __DIR__ . '/../routes/web.php';
}

// Run the application
$app->run();
