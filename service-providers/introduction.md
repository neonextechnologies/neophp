# Service Providers

Service providers are the central place for configuring and bootstrapping your application.

## What is a Service Provider?

A service provider is a class that:

- **Registers** services in the dependency injection container
- **Bootstraps** application components
- **Configures** third-party packages
- **Sets up** application infrastructure

## Basic Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(MyService::class, function($app) {
            return new MyService($app->config);
        });
    }
    
    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Boot application services
    }
}
```

## Service Provider Lifecycle

### 1. Registration Phase

All providers' `register()` methods are called first:

```php
public function register(): void
{
    // Bind services to container
    $this->app->bind(Interface::class, Implementation::class);
    
    // Register singletons
    $this->app->singleton(Service::class);
    
    // Register config
    $this->mergeConfigFrom(__DIR__.'/../config/myconfig.php', 'myconfig');
}
```

### 2. Boot Phase

After all providers registered, `boot()` methods are called:

```php
public function boot(): void
{
    // All services now available
    $service = $this->app->make(MyService::class);
    
    // Configure routes
    $this->loadRoutes();
    
    // Register views
    $this->loadViews();
    
    // Set up middleware
    $this->registerMiddleware();
}
```

## Registering Services

### Simple Binding

```php
public function register(): void
{
    $this->app->bind(UserRepository::class, EloquentUserRepository::class);
}
```

Usage:

```php
$repo = app(UserRepository::class);  // New instance each time
```

### Singleton Binding

```php
public function register(): void
{
    $this->app->singleton(Cache::class, function($app) {
        return new RedisCache($app->config['cache']);
    });
}
```

Usage:

```php
$cache = app(Cache::class);  // Same instance every time
```

### Instance Binding

```php
public function register(): void
{
    $cache = new RedisCache(config('cache'));
    $this->app->instance(Cache::class, $cache);
}
```

### Contextual Binding

```php
public function register(): void
{
    $this->app->when(PhotoController::class)
        ->needs(Storage::class)
        ->give(S3Storage::class);
    
    $this->app->when(VideoController::class)
        ->needs(Storage::class)
        ->give(LocalStorage::class);
}
```

### Tagging

```php
public function register(): void
{
    $this->app->tag([
        SpeedReport::class,
        MemoryReport::class,
    ], 'reports');
}
```

Usage:

```php
$reports = app()->tagged('reports');
foreach ($reports as $report) {
    $report->generate();
}
```

## Bootstrapping

### Load Configuration

```php
public function boot(): void
{
    // Merge config files
    $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');
    
    // Publish config
    $this->publishes([
        __DIR__.'/../config/services.php' => config_path('services.php'),
    ], 'config');
}
```

### Load Routes

```php
public function boot(): void
{
    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    
    // Or with Route facade
    Route::middleware('web')
        ->group(__DIR__.'/../routes/web.php');
}
```

### Load Views

```php
public function boot(): void
{
    $this->loadViewsFrom(__DIR__.'/../views', 'mypackage');
    
    // Publish views
    $this->publishes([
        __DIR__.'/../views' => resource_path('views/vendor/mypackage'),
    ], 'views');
}
```

### Load Migrations

```php
public function boot(): void
{
    $this->loadMigrationsFrom(__DIR__.'/../migrations');
}
```

### Load Translations

```php
public function boot(): void
{
    $this->loadTranslationsFrom(__DIR__.'/../lang', 'mypackage');
    
    // Publish translations
    $this->publishes([
        __DIR__.'/../lang' => resource_path('lang/vendor/mypackage'),
    ], 'lang');
}
```

## Complete Examples

### Database Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Database\DatabaseManager;
use NeoPhp\Database\Connection;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database services
     */
    public function register(): void
    {
        // Register database manager
        $this->app->singleton('db', function($app) {
            return new DatabaseManager($app);
        });
        
        // Register default connection
        $this->app->singleton(Connection::class, function($app) {
            return $app['db']->connection();
        });
        
        // Register query builder
        $this->app->bind('db.query', function($app) {
            return $app['db']->table();
        });
    }
    
    /**
     * Bootstrap database services
     */
    public function boot(): void
    {
        // Set up event listeners
        $this->app['db']->listen(function($query) {
            logger()->debug('Query executed', [
                'sql' => $query->sql,
                'time' => $query->time
            ]);
        });
        
        // Load migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }
}
```

### Cache Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Cache\CacheManager;
use NeoPhp\Cache\Stores\{RedisStore, FileStore, MemcachedStore};

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register cache services
     */
    public function register(): void
    {
        $this->app->singleton('cache', function($app) {
            return new CacheManager($app);
        });
        
        // Register stores
        $this->app->singleton('cache.store.redis', function($app) {
            return new RedisStore(
                $app['redis'],
                $app['config']['cache.prefix']
            );
        });
        
        $this->app->singleton('cache.store.file', function($app) {
            return new FileStore(
                $app['files'],
                $app['config']['cache.path']
            );
        });
    }
    
    /**
     * Bootstrap cache services
     */
    public function boot(): void
    {
        // Set default driver
        $this->app['cache']->setDefaultDriver(
            $this->app['config']['cache.default']
        );
    }
}
```

### Mail Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Mail\MailManager;
use NeoPhp\Mail\Transports\{SmtpTransport, SendmailTransport};

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register mail services
     */
    public function register(): void
    {
        $this->app->singleton('mail', function($app) {
            return new MailManager($app);
        });
        
        // Register transports
        $this->app->bind('mail.transport.smtp', function($app) {
            return new SmtpTransport(
                $app['config']['mail.smtp']
            );
        });
        
        $this->app->bind('mail.transport.sendmail', function($app) {
            return new SendmailTransport();
        });
    }
    
    /**
     * Bootstrap mail services
     */
    public function boot(): void
    {
        // Set default mailer
        $this->app['mail']->setDefaultMailer(
            $this->app['config']['mail.default']
        );
        
        // Configure global from address
        $this->app['mail']->alwaysFrom(
            $this->app['config']['mail.from.address'],
            $this->app['config']['mail.from.name']
        );
    }
}
```

### Event Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Events\Dispatcher;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event listeners
     */
    protected array $listen = [
        'user.created' => [
            Listeners\SendWelcomeEmail::class,
            Listeners\CreateUserProfile::class,
        ],
        'order.placed' => [
            Listeners\ProcessPayment::class,
            Listeners\SendOrderConfirmation::class,
            Listeners\UpdateInventory::class,
        ],
    ];
    
    /**
     * Register event services
     */
    public function register(): void
    {
        $this->app->singleton('events', function($app) {
            return new Dispatcher($app);
        });
    }
    
    /**
     * Bootstrap event services
     */
    public function boot(): void
    {
        // Register event listeners
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }
    }
}
```

### Validation Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Validation\{Validator, Factory};

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register validation services
     */
    public function register(): void
    {
        $this->app->singleton('validator', function($app) {
            return new Factory($app);
        });
    }
    
    /**
     * Bootstrap validation services
     */
    public function boot(): void
    {
        // Register custom rules
        Validator::extend('phone', function($attribute, $value) {
            return preg_match('/^[0-9]{10}$/', $value);
        }, 'The :attribute must be a valid phone number.');
        
        Validator::extend('strong_password', function($attribute, $value) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $value);
        }, 'The :attribute must contain uppercase, lowercase, and numbers.');
    }
}
```

## Deferred Providers

Defer loading until service is needed:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class HeavyServiceProvider extends ServiceProvider
{
    /**
     * Defer loading
     */
    protected bool $defer = true;
    
    /**
     * Services provided
     */
    public function provides(): array
    {
        return [HeavyService::class];
    }
    
    /**
     * Register when needed
     */
    public function register(): void
    {
        $this->app->singleton(HeavyService::class, function($app) {
            // Expensive initialization
            return new HeavyService();
        });
    }
}
```

## Registering Providers

Add to `config/app.php`:

```php
return [
    'providers' => [
        // Framework providers
        NeoPhp\Database\DatabaseServiceProvider::class,
        NeoPhp\Cache\CacheServiceProvider::class,
        NeoPhp\Mail\MailServiceProvider::class,
        
        // Application providers
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],
];
```

## Best Practices

### 1. Single Responsibility

Each provider should handle one aspect:

```php
// Good ✅
class DatabaseServiceProvider  // Only database
class CacheServiceProvider     // Only cache
class MailServiceProvider      // Only mail

// Bad ❌
class ServiceProvider  // Everything
```

### 2. Use Register for Bindings

```php
// Good ✅
public function register(): void
{
    $this->app->bind(Interface::class, Implementation::class);
}

// Bad ❌
public function boot(): void
{
    $this->app->bind(Interface::class, Implementation::class);
}
```

### 3. Use Boot for Configuration

```php
// Good ✅
public function boot(): void
{
    $this->loadRoutes();
    $this->loadViews();
}

// Bad ❌
public function register(): void
{
    $this->loadRoutes();  // Dependencies may not be available
}
```

### 4. Defer Heavy Providers

```php
// Good ✅
protected bool $defer = true;

// For services not used on every request
```

### 5. Type Hint Dependencies

```php
// Good ✅
public function boot(Router $router, Config $config): void
{
    // Dependencies injected
}

// Bad ❌
public function boot(): void
{
    $router = app('router');  // Manual resolution
}
```

## Next Steps

- [Dependency Injection](dependency-injection.md)
- [Container](container.md)
- [Facades](facades.md)
- [Plugins](../plugins/introduction.md)
