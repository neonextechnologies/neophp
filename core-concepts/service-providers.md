# Service Providers

Service Providers are the central place to configure and bootstrap your application's services. They're where you register service container bindings, event listeners, middleware, and even routes.

## What are Service Providers?

Service Providers are classes that:
- Register services into the container
- Bootstrap application services
- Organize code by concern
- Enable modular architecture

Think of them as the "glue" that binds your application together.

## Basic Structure

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     */
    public function register(): void
    {
        // Register bindings
        $this->app->singleton('service', function($app) {
            return new MyService();
        });
    }

    /**
     * Bootstrap application services
     */
    public function boot(): void
    {
        // Bootstrap code (runs after all providers are registered)
    }
}
```

## Register vs Boot

### register()
- Called first for ALL providers
- Use for binding services
- Don't access other services here
- Keep it simple and fast

### boot()
- Called after ALL providers are registered
- Use for bootstrapping
- Can access other services
- Register routes, views, event listeners

```php
class PaymentServiceProvider extends ServiceProvider
{
    // register() - Bind services
    public function register(): void
    {
        $this->app->singleton('payment.stripe', function($app) {
            return new StripePayment(config('payment.stripe_key'));
        });
        
        $this->app->singleton('payment.paypal', function($app) {
            return new PayPalPayment(config('payment.paypal_key'));
        });
    }

    // boot() - Bootstrap services
    public function boot(): void
    {
        // Now we can access other services
        $router = $this->app->make('router');
        $router->group(['prefix' => 'api/payment'], function($router) {
            require __DIR__ . '/../routes/payment.php';
        });
    }
}
```

## Binding Services

### Singleton Binding

Create once, reuse everywhere:

```php
$this->app->singleton('db', function($app) {
    return new Database(config('database'));
});

// Always returns the same instance
$db1 = app('db');
$db2 = app('db');
// $db1 === $db2 (true)
```

### Regular Binding

Create new instance every time:

```php
$this->app->bind('mailer', function($app) {
    return new Mailer(config('mail'));
});

// Returns new instance each time
$mailer1 = app('mailer');
$mailer2 = app('mailer');
// $mailer1 === $mailer2 (false)
```

### Instance Binding

Bind an existing instance:

```php
$logger = new Logger();
$this->app->instance('logger', $logger);

// Always returns this exact instance
$log = app('logger');
```

### Interface Binding

Bind interfaces to implementations:

```php
$this->app->singleton(
    \NeoPhp\Contracts\CacheInterface::class,
    \App\Services\RedisCache::class
);

// Automatically resolves
public function __construct(CacheInterface $cache) {
    // Gets RedisCache instance
}
```

## Service Provider Examples

### Database Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Contracts\DatabaseInterface;
use App\Services\Database\MySQLDatabase;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseInterface::class, function($app) {
            return new MySQLDatabase([
                'host' => config('database.host'),
                'port' => config('database.port'),
                'database' => config('database.database'),
                'username' => config('database.username'),
                'password' => config('database.password'),
            ]);
        });
        
        // Alias for convenience
        $this->app->alias(DatabaseInterface::class, 'db');
    }

    public function boot(): void
    {
        // Connect to database
        $this->app->make(DatabaseInterface::class)->connect();
    }
}
```

### Cache Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Contracts\CacheInterface;
use App\Services\Cache\FileCache;
use App\Services\Cache\RedisCache;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheInterface::class, function($app) {
            $driver = config('cache.driver', 'file');
            
            return match($driver) {
                'redis' => new RedisCache(config('cache.redis')),
                'file' => new FileCache(config('cache.path')),
                default => throw new \Exception("Unknown cache driver: $driver")
            };
        });
        
        $this->app->alias(CacheInterface::class, 'cache');
    }
}
```

### Mail Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Contracts\MailerInterface;
use App\Services\Mail\SMTPMailer;

class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MailerInterface::class, function($app) {
            return new SMTPMailer([
                'host' => config('mail.host'),
                'port' => config('mail.port'),
                'username' => config('mail.username'),
                'password' => config('mail.password'),
                'encryption' => config('mail.encryption'),
            ]);
        });
        
        $this->app->alias(MailerInterface::class, 'mail');
    }

    public function boot(): void
    {
        // Set default from address
        $mailer = $this->app->make(MailerInterface::class);
        $mailer->setFrom(
            config('mail.from.address'),
            config('mail.from.name')
        );
    }
}
```

### Route Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutes();
    }

    protected function loadRoutes(): void
    {
        // Web routes
        require base_path('routes/web.php');
        
        // API routes
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }
}
```

## Provider Discovery

NeoPhp automatically discovers providers in `app/Providers/`:

```
app/Providers/
├── AppServiceProvider.php       # Auto-discovered
├── DatabaseServiceProvider.php  # Auto-discovered
├── CacheServiceProvider.php     # Auto-discovered
└── RouteServiceProvider.php     # Auto-discovered
```

### Manual Registration

You can also register providers manually:

```php
// bootstrap/app.php
$providerManager = new ProviderManager($container);

$providerManager->registerProviders([
    \App\Providers\AppServiceProvider::class,
    \App\Providers\DatabaseServiceProvider::class,
    \App\Providers\CacheServiceProvider::class,
]);
```

## Deferred Providers

Defer loading until the service is actually needed:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred
     */
    protected bool $defer = true;

    /**
     * Services provided by this provider
     */
    protected array $provides = ['payment', 'payment.stripe', 'payment.paypal'];

    public function register(): void
    {
        $this->app->singleton('payment', function($app) {
            return new PaymentGateway();
        });
    }

    /**
     * Check if provider is deferred
     */
    public function isDeferred(): bool
    {
        return $this->defer;
    }

    /**
     * Get services provided
     */
    public function provides(): array
    {
        return $this->provides;
    }
}
```

The provider won't be loaded until you actually use it:

```php
// Provider NOT loaded yet
$app->boot();

// Provider loaded NOW (when first accessed)
$payment = app('payment');
```

## Provider Dependencies

Declare dependencies on other providers:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Provider dependencies
     */
    protected array $dependencies = [
        DatabaseServiceProvider::class,
        CacheServiceProvider::class,
        MailServiceProvider::class,
    ];

    public function register(): void
    {
        $this->app->singleton('orders', function($app) {
            // These are guaranteed to be available
            return new OrderService(
                $app->make('db'),
                $app->make('cache'),
                $app->make('mail')
            );
        });
    }
}
```

NeoPhp ensures dependencies are loaded first.

## Provider Lifecycle

```
1. Register Phase
   ├─ Load all providers
   ├─ Call register() on each
   └─ Bind services to container

2. Boot Phase
   ├─ Check dependencies
   ├─ Call boot() on each
   └─ Application is ready
```

## Helper Functions

### app()

Access the application container:

```php
// Get service
$cache = app('cache');

// Get with class name
$cache = app(CacheInterface::class);

// Bind service
app()->singleton('service', function() {
    return new MyService();
});
```

### resolve()

Resolve a service from the container:

```php
$cache = resolve(CacheInterface::class);
```

### make()

Make an instance:

```php
$service = app()->make(MyService::class);
```

## Best Practices

### 1. One Concern Per Provider

```php
// Good ✅
class DatabaseServiceProvider extends ServiceProvider {
    // Only database-related bindings
}

class CacheServiceProvider extends ServiceProvider {
    // Only cache-related bindings
}

// Bad ❌
class AppServiceProvider extends ServiceProvider {
    // Database, cache, mail, queue, everything...
}
```

### 2. Use Deferred Loading

```php
// For expensive services
class SearchServiceProvider extends ServiceProvider {
    protected bool $defer = true; // Don't load unless needed
    protected array $provides = ['search'];
}
```

### 3. Declare Dependencies

```php
protected array $dependencies = [
    DatabaseServiceProvider::class,
    CacheServiceProvider::class,
];
```

### 4. Keep register() Simple

```php
// Good ✅
public function register(): void {
    $this->app->singleton('cache', fn() => new FileCache());
}

// Bad ❌
public function register(): void {
    $cache = new FileCache();
    $cache->connect();
    $cache->warmup();
    // Too much work in register()
}
```

### 5. Use boot() for Setup

```php
public function boot(): void {
    // Load routes
    $this->loadRoutes();
    
    // Register middleware
    $this->registerMiddleware();
    
    // Register event listeners
    $this->registerEventListeners();
}
```

## Next Steps

- [Plugins](plugins.md)
- [Deferred Providers](../providers/deferred-providers.md)
- [Provider Dependencies](../providers/dependencies.md)
