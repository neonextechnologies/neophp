# Plugin API Reference

Complete API reference for NeoPhp plugin development.

## AbstractPlugin

Base class for all plugins.

### Properties

```php
protected Application $app;          // Application instance
protected string $name;               // Plugin name
protected string $path;               // Plugin directory path
protected array $config;              // Plugin configuration
protected array $hooks = [];          // Registered hooks
```

### Methods

#### activate()

Runs when plugin is activated:

```php
public function activate(): void
{
    // Run migrations
    $this->runMigrations();
    
    // Create default data
    $this->createDefaults();
}
```

#### deactivate()

Runs when plugin is deactivated:

```php
public function deactivate(): void
{
    // Clean up temporary data
    Cache::forget('plugin.*');
}
```

#### boot()

Runs on every request:

```php
public function boot(): void
{
    $this->loadRoutes();
    $this->loadViews();
    $this->registerServices();
}
```

#### uninstall()

Runs when plugin is completely removed:

```php
public function uninstall(): void
{
    // Drop tables
    Schema::dropIfExists('plugin_table');
    
    // Remove settings
    Setting::where('key', 'like', 'plugin.%')->delete();
}
```

### Path Helpers

#### path()

Get plugin file path:

```php
$configPath = $this->path('config/plugin.php');
$viewPath = $this->path('views/index.php');
```

#### url()

Get plugin URL:

```php
$assetUrl = $this->url('assets/css/style.css');
// Returns: /plugins/my-plugin/assets/css/style.css
```

#### asset()

Get plugin asset URL:

```php
$css = $this->asset('css/style.css');
// Returns: /plugins/my-plugin/assets/css/style.css
```

### Configuration

#### config()

Get configuration value:

```php
$value = $this->config('api.key');
$default = $this->config('api.timeout', 30);
```

#### setting()

Get/set plugin settings:

```php
// Get
$value = $this->setting('option_name');

// Set
$this->setting('option_name', 'value');

// Get with default
$value = $this->setting('option_name', 'default');
```

### Routes

#### loadRoutes()

Load plugin routes:

```php
protected function loadRoutes(): void
{
    // Web routes
    if (file_exists($this->path('routes/web.php'))) {
        require $this->path('routes/web.php');
    }
    
    // API routes
    if (file_exists($this->path('routes/api.php'))) {
        Route::prefix('api')
            ->middleware('api')
            ->group($this->path('routes/api.php'));
    }
}
```

#### route()

Generate route URL:

```php
$url = $this->route('plugin.action');
$url = $this->route('plugin.show', ['id' => 1]);
```

### Views

#### loadViews()

Register view namespace:

```php
protected function loadViews(): void
{
    $this->app->view->addNamespace('plugin', $this->path('views'));
}
```

#### view()

Render plugin view:

```php
return $this->view('plugin::index', ['data' => $data]);
```

### Services

#### registerServices()

Register services in container:

```php
protected function registerServices(): void
{
    $this->app->singleton(MyService::class);
    
    $this->app->bind(MyInterface::class, MyImplementation::class);
}
```

#### service()

Get service from container:

```php
$service = $this->service(MyService::class);
```

### Commands

#### commands()

Register CLI commands:

```php
protected function registerCommands(): void
{
    $this->commands([
        Commands\MyCommand::class,
        Commands\AnotherCommand::class,
    ]);
}
```

### Database

#### runMigrations()

Run plugin migrations:

```php
protected function runMigrations(): void
{
    $migrator = $this->app->make('migrator');
    $migrator->run($this->path('migrations'));
}
```

#### table()

Get table name with prefix:

```php
$tableName = $this->table('posts');
// Returns: wp_plugin_posts (with prefix)
```

### Hooks

#### addAction()

Register action hook:

```php
$this->addAction('user.created', [$this, 'onUserCreated']);
$this->addAction('user.created', [$this, 'onUserCreated'], 10);  // Priority
```

#### addFilter()

Register filter hook:

```php
$this->addFilter('post.content', [$this, 'filterContent']);
$this->addFilter('post.content', [$this, 'filterContent'], 10, 2);  // Priority, params
```

#### removeAction()

Remove action hook:

```php
$this->removeAction('user.created', [$this, 'onUserCreated']);
```

#### removeFilter()

Remove filter hook:

```php
$this->removeFilter('post.content', [$this, 'filterContent']);
```

### Assets

#### enqueueStyle()

Enqueue CSS file:

```php
$this->enqueueStyle('plugin-style', $this->asset('css/style.css'));
$this->enqueueStyle('plugin-style', $this->asset('css/style.css'), ['bootstrap']);
```

#### enqueueScript()

Enqueue JavaScript file:

```php
$this->enqueueScript('plugin-script', $this->asset('js/script.js'));
$this->enqueueScript('plugin-script', $this->asset('js/script.js'), ['jquery']);
```

#### localizeScript()

Pass data to JavaScript:

```php
$this->localizeScript('plugin-script', 'pluginData', [
    'apiUrl' => '/api/plugin',
    'nonce' => wp_create_nonce('plugin')
]);
```

### Scheduling

#### schedule()

Schedule tasks:

```php
protected function schedule(): void
{
    $this->app->scheduler->daily('plugin:cleanup')
        ->at('02:00')
        ->description('Clean up plugin data');
}
```

### Cache

#### cache()

Cache operations:

```php
// Get
$value = $this->cache()->get('key');

// Put
$this->cache()->put('key', 'value', 3600);

// Remember
$value = $this->cache()->remember('key', 3600, function() {
    return expensive_operation();
});

// Forget
$this->cache()->forget('key');

// Flush plugin cache
$this->cache()->tags(['plugin'])->flush();
```

### Events

#### fire()

Fire event:

```php
$this->fire('plugin.event', $data);
```

#### listen()

Listen to event:

```php
$this->listen('app.booted', function() {
    // Do something
});
```

## Plugin Hooks

### Available Actions

#### plugin.activated

Fired after plugin activation:

```php
do_action('plugin.activated', $plugin);
```

#### plugin.deactivated

Fired after plugin deactivation:

```php
do_action('plugin.deactivated', $plugin);
```

#### plugin.installed

Fired after plugin installation:

```php
do_action('plugin.installed', $plugin);
```

#### plugin.uninstalled

Fired after plugin uninstallation:

```php
do_action('plugin.uninstalled', $plugin);
```

### Available Filters

#### plugin.config

Filter plugin configuration:

```php
$config = apply_filters('plugin.config', $config, $plugin);
```

#### plugin.routes

Filter plugin routes:

```php
$routes = apply_filters('plugin.routes', $routes, $plugin);
```

#### plugin.views

Filter plugin views:

```php
$views = apply_filters('plugin.views', $views, $plugin);
```

## Complete Plugin Example

### plugin.json

```json
{
    "name": "analytics",
    "title": "Analytics Plugin",
    "description": "Track website analytics",
    "version": "1.0.0",
    "author": "Your Name",
    "namespace": "Analytics",
    "requires": {
        "neophp": "^1.0",
        "php": "^8.1"
    },
    "autoload": {
        "psr-4": {
            "Analytics\\": "src/"
        }
    }
}
```

### Plugin.php

```php
<?php

namespace Analytics;

use NeoPhp\Foundation\Plugin\AbstractPlugin;
use Analytics\Services\AnalyticsService;

class Plugin extends AbstractPlugin
{
    /**
     * Plugin activation
     */
    public function activate(): void
    {
        $this->runMigrations();
        $this->createSettings();
    }
    
    /**
     * Plugin boot
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutes();
        
        // Load views
        $this->loadViews();
        
        // Register services
        $this->app->singleton(AnalyticsService::class);
        
        // Register commands
        $this->commands([
            Commands\GenerateReportCommand::class,
        ]);
        
        // Register hooks
        $this->addAction('page.viewed', [$this, 'trackPageView']);
        $this->addFilter('dashboard.widgets', [$this, 'addWidget']);
        
        // Schedule tasks
        $this->schedule();
        
        // Enqueue assets
        $this->enqueueAssets();
    }
    
    /**
     * Track page view
     */
    public function trackPageView($page): void
    {
        $service = $this->service(AnalyticsService::class);
        $service->trackPageView($page);
    }
    
    /**
     * Add dashboard widget
     */
    public function addWidget(array $widgets): array
    {
        $widgets[] = [
            'id' => 'analytics-widget',
            'title' => 'Analytics',
            'content' => $this->view('analytics::widget')
        ];
        
        return $widgets;
    }
    
    /**
     * Schedule tasks
     */
    protected function schedule(): void
    {
        $this->app->scheduler
            ->command('analytics:report')
            ->daily()
            ->at('01:00');
    }
    
    /**
     * Enqueue assets
     */
    protected function enqueueAssets(): void
    {
        $this->enqueueStyle('analytics', $this->asset('css/analytics.css'));
        $this->enqueueScript('analytics', $this->asset('js/analytics.js'), ['jquery']);
        
        $this->localizeScript('analytics', 'analyticsData', [
            'apiUrl' => $this->route('analytics.api'),
            'trackingId' => $this->config('tracking_id')
        ]);
    }
    
    /**
     * Create default settings
     */
    protected function createSettings(): void
    {
        $this->setting('enabled', true);
        $this->setting('tracking_id', '');
        $this->setting('exclude_admin', true);
    }
}
```

### Service Example

```php
<?php

namespace Analytics\Services;

class AnalyticsService
{
    /**
     * Track page view
     */
    public function trackPageView(string $url): void
    {
        PageView::create([
            'url' => $url,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('Referer'),
        ]);
        
        // Fire hook
        do_action('analytics.page_viewed', $url);
    }
    
    /**
     * Get popular pages
     */
    public function getPopularPages(int $limit = 10): array
    {
        return PageView::select('url', DB::raw('COUNT(*) as views'))
            ->groupBy('url')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    
    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_views' => PageView::count(),
            'unique_visitors' => PageView::distinct('ip_address')->count(),
            'pages' => PageView::distinct('url')->count(),
            'avg_time_on_site' => $this->getAverageTimeOnSite(),
        ];
    }
}
```

### Command Example

```php
<?php

namespace Analytics\Commands;

use NeoPhp\Foundation\Console\Command;
use Analytics\Services\AnalyticsService;

class GenerateReportCommand extends Command
{
    protected string $signature = 'analytics:report {--email=}';
    protected string $description = 'Generate analytics report';
    
    public function handle(): int
    {
        $this->info('Generating analytics report...');
        
        $service = app(AnalyticsService::class);
        $stats = $service->getStatistics();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Views', $stats['total_views']],
                ['Unique Visitors', $stats['unique_visitors']],
                ['Pages', $stats['pages']],
            ]
        );
        
        if ($email = $this->option('email')) {
            $this->sendReport($email, $stats);
            $this->success("Report sent to {$email}");
        }
        
        return 0;
    }
}
```

## Best Practices

### 1. Use Type Hints

```php
// Good ✅
public function trackPageView(string $url): void
{
    // ...
}

// Bad ❌
public function trackPageView($url)
{
    // ...
}
```

### 2. Handle Errors

```php
// Good ✅
try {
    $result = $this->performAction();
} catch (Exception $e) {
    logger()->error('Plugin error: ' . $e->getMessage());
    return false;
}

// Bad ❌
$result = $this->performAction();  // May throw
```

### 3. Cache Expensive Operations

```php
// Good ✅
$stats = $this->cache()->remember('analytics.stats', 3600, function() {
    return $this->calculateStatistics();
});

// Bad ❌
$stats = $this->calculateStatistics();  // Every request
```

### 4. Provide Hooks

```php
// Good ✅
do_action('plugin.before_action');
$result = $this->performAction();
do_action('plugin.after_action', $result);

// Bad ❌
$result = $this->performAction();  // No extensibility
```

### 5. Document Everything

```php
/**
 * Track page view
 *
 * @param string $url Page URL
 * @return void
 * @fires analytics.page_viewed
 */
public function trackPageView(string $url): void
{
    // ...
}
```

## Next Steps

- [Plugin Development](development.md)
- [Hooks System](../core/hooks.md)
- [Service Providers](../service-providers/introduction.md)
- [Testing](../testing/introduction.md)
