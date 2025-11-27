# Service Container

The service container is a powerful tool for managing class dependencies and performing dependency injection.

## What is the Container?

The container is a registry that:

- **Resolves** class dependencies automatically
- **Manages** object lifecycles (singleton, transient)
- **Binds** interfaces to implementations
- **Provides** dependency injection throughout the application

## Basic Usage

### Binding

```php
use NeoPhp\Foundation\Application;

$app = new Application();

// Simple binding
$app->bind(MyService::class, function($app) {
    return new MyService();
});

// Get instance
$service = $app->make(MyService::class);
```

### Singleton

```php
// Singleton - same instance every time
$app->singleton(Cache::class, function($app) {
    return new RedisCache($app['config']['redis']);
});

$cache1 = $app->make(Cache::class);
$cache2 = $app->make(Cache::class);
// $cache1 === $cache2 (same instance)
```

### Instance

```php
// Bind existing instance
$cache = new RedisCache(['host' => '127.0.0.1']);
$app->instance(Cache::class, $cache);

$retrieved = $app->make(Cache::class);
// $retrieved === $cache
```

## Automatic Resolution

The container automatically resolves dependencies:

```php
class UserController
{
    public function __construct(
        private UserRepository $repository,
        private Cache $cache,
        private Validator $validator
    ) {}
}

// Container automatically injects all dependencies
$controller = $app->make(UserController::class);
```

## Binding Types

### Closure Binding

```php
$app->bind(PaymentGateway::class, function($app) {
    $config = $app->make('config');
    
    if ($config->get('payment.gateway') === 'stripe') {
        return new StripeGateway(
            $config->get('payment.stripe.key')
        );
    }
    
    return new PayPalGateway(
        $config->get('payment.paypal.client_id')
    );
});
```

### Class Binding

```php
// Interface to implementation
$app->bind(
    UserRepositoryInterface::class,
    EloquentUserRepository::class
);
```

### Singleton Binding

```php
$app->singleton(Database::class, function($app) {
    return new Database([
        'host' => $app['config']['database.host'],
        'database' => $app['config']['database.name'],
        'username' => $app['config']['database.username'],
        'password' => $app['config']['database.password'],
    ]);
});
```

### Scoped Binding

```php
// New instance per HTTP request
$app->scoped(ShoppingCart::class);
```

## Contextual Binding

Different implementations for different contexts:

```php
$app->when(PhotoController::class)
    ->needs(StorageInterface::class)
    ->give(S3Storage::class);

$app->when(VideoController::class)
    ->needs(StorageInterface::class)
    ->give(LocalStorage::class);

$app->when(DocumentController::class)
    ->needs(StorageInterface::class)
    ->give(function($app) {
        return new LocalStorage('/path/to/documents');
    });
```

### Primitive Dependencies

```php
$app->when(ApiService::class)
    ->needs('$apiKey')
    ->give(config('services.api.key'));

$app->when(RateLimiter::class)
    ->needs('$maxAttempts')
    ->give(100);
```

## Tagging

Group related bindings:

```php
// Tag services
$app->tag([
    SpeedReport::class,
    MemoryReport::class,
    ErrorReport::class,
], 'reports');

// Resolve all tagged services
foreach ($app->tagged('reports') as $report) {
    $report->generate();
}
```

## Extending Bindings

Modify resolved instances:

```php
$app->singleton(PaymentGateway::class, StripeGateway::class);

// Extend after resolution
$app->extend(PaymentGateway::class, function($gateway, $app) {
    $gateway->enableLogging($app['logger']);
    return $gateway;
});
```

## Resolving

### make() Method

```php
$service = $app->make(UserService::class);

// With parameters
$service = $app->make(UserService::class, [
    'config' => ['option' => 'value']
]);
```

### makeWith() Method

```php
$service = $app->makeWith(UserService::class, [
    'repository' => new CustomRepository(),
    'cache' => new MemoryCache()
]);
```

### Array Access

```php
$app['cache'] = function($app) {
    return new RedisCache();
};

$cache = $app['cache'];
```

### Helper Functions

```php
// app() helper
$service = app(UserService::class);

// resolve() helper
$service = resolve(UserService::class);
```

## Checking Bindings

```php
// Check if bound
if ($app->bound(Cache::class)) {
    // Binding exists
}

// Check if resolved
if ($app->resolved(Cache::class)) {
    // Already resolved
}

// Check if singleton
if ($app->isShared(Cache::class)) {
    // Is singleton
}
```

## Rebinding

```php
$app->singleton('cache', RedisCache::class);

// Rebind callback when cache is rebound
$app->rebinding('cache', function($app, $cache) {
    // Do something when cache is rebound
});

// Rebind
$app->singleton('cache', MemcachedCache::class);
// Callback is triggered
```

## Aliasing

```php
// Create alias
$app->alias(Cache::class, 'cache');

// Both work
$cache = $app->make(Cache::class);
$cache = $app->make('cache');
```

## Events

### Resolving Event

```php
$app->resolving(UserService::class, function($service, $app) {
    // Called before UserService is returned
    $service->setLogger($app['logger']);
});

// All resolutions
$app->resolving(function($object, $app) {
    // Called for any resolution
});
```

### After Resolving Event

```php
$app->afterResolving(UserService::class, function($service, $app) {
    // Called after UserService is fully resolved
});
```

## Complete Examples

### API Client with Container

```php
<?php

namespace App\Services;

use NeoPhp\Http\Client;
use NeoPhp\Cache\Cache;

class ApiService
{
    public function __construct(
        private Client $http,
        private Cache $cache,
        private string $apiKey,
        private string $apiUrl
    ) {}
    
    public function get(string $endpoint): array
    {
        $cacheKey = "api.{$endpoint}";
        
        return $this->cache->remember($cacheKey, 3600, function() use ($endpoint) {
            $response = $this->http->get("{$this->apiUrl}/{$endpoint}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}"
                ]
            ]);
            
            return $response->json();
        });
    }
}

// Service Provider
class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiService::class, function($app) {
            return new ApiService(
                $app->make(Client::class),
                $app->make(Cache::class),
                $app['config']['api.key'],
                $app['config']['api.url']
            );
        });
    }
}
```

### Repository Pattern with Container

```php
<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function create(array $data): User;
}

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private Database $db,
        private Cache $cache
    ) {}
    
    public function find(int $id): ?User
    {
        return $this->cache->remember("user.{$id}", 3600, function() use ($id) {
            return User::find($id);
        });
    }
    
    public function create(array $data): User
    {
        $user = User::create($data);
        $this->cache->forget("user.{$user->id}");
        return $user;
    }
}

// Service Provider
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
    }
}

// Service using repository
class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}
    
    public function getUser(int $id): ?User
    {
        return $this->repository->find($id);
    }
}

// Controller
class UserController extends Controller
{
    public function show(int $id, UserService $service)
    {
        $user = $service->getUser($id);
        return $this->json($user);
    }
}
```

### Factory Pattern with Container

```php
<?php

namespace App\Services;

interface NotificationInterface
{
    public function send(string $message, array $recipients): void;
}

class EmailNotification implements NotificationInterface
{
    public function __construct(private Mailer $mailer) {}
    
    public function send(string $message, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            $this->mailer->to($recipient)->send($message);
        }
    }
}

class SmsNotification implements NotificationInterface
{
    public function __construct(private SmsClient $client) {}
    
    public function send(string $message, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            $this->client->sendSms($recipient, $message);
        }
    }
}

class PushNotification implements NotificationInterface
{
    public function __construct(private PushService $push) {}
    
    public function send(string $message, array $recipients): void
    {
        $this->push->sendToDevices($recipients, $message);
    }
}

class NotificationFactory
{
    public function __construct(private Container $container) {}
    
    public function create(string $channel): NotificationInterface
    {
        return match($channel) {
            'email' => $this->container->make(EmailNotification::class),
            'sms' => $this->container->make(SmsNotification::class),
            'push' => $this->container->make(PushNotification::class),
            default => throw new InvalidArgumentException("Unknown channel: {$channel}")
        };
    }
}

// Usage
class NotificationService
{
    public function __construct(private NotificationFactory $factory) {}
    
    public function notify(string $channel, string $message, array $recipients): void
    {
        $notification = $this->factory->create($channel);
        $notification->send($message, $recipients);
    }
}
```

### Caching Strategy with Container

```php
<?php

namespace App\Services;

interface CacheInterface
{
    public function get(string $key);
    public function put(string $key, $value, int $ttl);
    public function forget(string $key);
}

class RedisCache implements CacheInterface
{
    public function __construct(private Redis $redis) {}
    
    public function get(string $key)
    {
        return unserialize($this->redis->get($key));
    }
    
    public function put(string $key, $value, int $ttl)
    {
        $this->redis->setex($key, $ttl, serialize($value));
    }
    
    public function forget(string $key)
    {
        $this->redis->del($key);
    }
}

class FileCache implements CacheInterface
{
    public function __construct(private string $path) {}
    
    public function get(string $key)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires_at'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    public function put(string $key, $value, int $ttl)
    {
        $file = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];
        
        file_put_contents($file, serialize($data));
    }
    
    public function forget(string $key)
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    private function getFilePath(string $key): string
    {
        return $this->path . '/' . md5($key) . '.cache';
    }
}

// Service Provider
class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheInterface::class, function($app) {
            $driver = $app['config']['cache.driver'];
            
            if ($driver === 'redis') {
                return new RedisCache($app->make(Redis::class));
            }
            
            return new FileCache($app['config']['cache.path']);
        });
    }
}
```

## Container Configuration

### config/app.php

```php
return [
    'providers' => [
        // Framework providers
        NeoPhp\Foundation\Providers\AppServiceProvider::class,
        NeoPhp\Foundation\Providers\DatabaseServiceProvider::class,
        NeoPhp\Foundation\Providers\CacheServiceProvider::class,
        
        // Application providers
        App\Providers\RepositoryServiceProvider::class,
        App\Providers\ApiServiceProvider::class,
    ],
    
    'aliases' => [
        'Cache' => NeoPhp\Cache\Facades\Cache::class,
        'DB' => NeoPhp\Database\Facades\DB::class,
        'Mail' => NeoPhp\Mail\Facades\Mail::class,
    ],
];
```

## PSR-11 Container Interface

NeoPhp container implements PSR-11:

```php
use Psr\Container\ContainerInterface;

class MyService
{
    public function __construct(private ContainerInterface $container) {}
    
    public function doSomething()
    {
        if ($this->container->has(Cache::class)) {
            $cache = $this->container->get(Cache::class);
        }
    }
}
```

## Best Practices

### 1. Bind Interfaces, Not Implementations

```php
// Good ✅
$app->bind(CacheInterface::class, RedisCache::class);

// Bad ❌
$app->bind(RedisCache::class, RedisCache::class);
```

### 2. Use Singletons for Stateless Services

```php
// Good ✅
$app->singleton(Logger::class);
$app->singleton(Cache::class);

// Bad ❌
$app->bind(Logger::class);  // New logger instance each time
```

### 3. Use Scoped for Request-Specific Data

```php
// Good ✅
$app->scoped(ShoppingCart::class);  // Per request

// Bad ❌
$app->singleton(ShoppingCart::class);  // Shared across requests
```

### 4. Avoid Service Locator Pattern

```php
// Good ✅ - Constructor injection
public function __construct(Cache $cache) {}

// Bad ❌ - Service locator
public function index() {
    $cache = app(Cache::class);
}
```

### 5. Type Hint Dependencies

```php
// Good ✅
public function __construct(UserRepository $repository) {}

// Bad ❌
public function __construct($repository) {}
```

## Performance Optimization

### Binding Resolution Caching

```php
// Cache bindings for production
$app->cached(function($app) {
    // Register all bindings
});
```

### Deferred Providers

```php
class HeavyServiceProvider extends ServiceProvider
{
    protected bool $defer = true;
    
    public function provides(): array
    {
        return [HeavyService::class];
    }
}
```

## Next Steps

- [Service Providers](introduction.md)
- [Dependency Injection](dependency-injection.md)
- [Facades](facades.md)
- [Testing](../testing/introduction.md)
