# Dependency Injection

NeoPhp uses dependency injection to manage class dependencies and perform inversion of control.

## What is Dependency Injection?

Dependency injection is a technique where objects receive their dependencies from external sources rather than creating them internally.

### Without Dependency Injection

```php
class UserController
{
    public function index()
    {
        // Creating dependencies internally - tight coupling
        $db = new Database();
        $cache = new Cache();
        $users = $db->query('SELECT * FROM users');
        
        return view('users', compact('users'));
    }
}
```

### With Dependency Injection

```php
class UserController
{
    public function __construct(
        private Database $db,
        private Cache $cache
    ) {}
    
    public function index()
    {
        // Dependencies injected - loose coupling
        $users = $this->db->query('SELECT * FROM users');
        
        return view('users', compact('users'));
    }
}
```

## Constructor Injection

The most common form - dependencies passed through constructor:

```php
<?php

namespace App\Services;

use App\Repositories\UserRepository;
use NeoPhp\Cache\Cache;

class UserService
{
    public function __construct(
        private UserRepository $repository,
        private Cache $cache
    ) {}
    
    public function getUser(int $id): User
    {
        return $this->cache->remember("user.{$id}", 3600, function() use ($id) {
            return $this->repository->find($id);
        });
    }
}
```

## Method Injection

Dependencies injected into specific methods:

```php
use NeoPhp\Http\Request;

class UserController extends Controller
{
    public function store(Request $request, UserService $service)
    {
        // $request and $service automatically injected
        $user = $service->create($request->all());
        
        return $this->json($user, 201);
    }
}
```

## Property Injection

Less common, but supported:

```php
class UserController extends Controller
{
    #[Inject]
    private UserService $service;
    
    #[Inject]
    private Cache $cache;
    
    public function index()
    {
        return $this->service->getAll();
    }
}
```

## Binding Interfaces to Implementations

### Basic Binding

```php
// In service provider
public function register(): void
{
    $this->app->bind(
        UserRepositoryInterface::class,
        EloquentUserRepository::class
    );
}
```

Usage:

```php
class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository  // EloquentUserRepository injected
    ) {}
}
```

### Singleton Binding

Same instance shared across application:

```php
public function register(): void
{
    $this->app->singleton(
        CacheInterface::class,
        RedisCache::class
    );
}
```

### Instance Binding

Bind existing instance:

```php
public function register(): void
{
    $cache = new RedisCache([
        'host' => '127.0.0.1',
        'port' => 6379
    ]);
    
    $this->app->instance(CacheInterface::class, $cache);
}
```

## Contextual Binding

Different implementations for different contexts:

```php
public function register(): void
{
    // PhotoController gets S3Storage
    $this->app->when(PhotoController::class)
        ->needs(StorageInterface::class)
        ->give(S3Storage::class);
    
    // VideoController gets LocalStorage
    $this->app->when(VideoController::class)
        ->needs(StorageInterface::class)
        ->give(LocalStorage::class);
}
```

Usage:

```php
class PhotoController
{
    public function __construct(
        private StorageInterface $storage  // S3Storage injected
    ) {}
}

class VideoController
{
    public function __construct(
        private StorageInterface $storage  // LocalStorage injected
    ) {}
}
```

## Binding with Closures

Custom instantiation logic:

```php
public function register(): void
{
    $this->app->bind(PaymentGateway::class, function($app) {
        $config = $app->make('config');
        
        if ($config->get('payment.gateway') === 'stripe') {
            return new StripeGateway($config->get('payment.stripe'));
        }
        
        return new PayPalGateway($config->get('payment.paypal'));
    });
}
```

## Automatic Resolution

NeoPhp automatically resolves dependencies:

```php
class OrderService
{
    public function __construct(
        private UserRepository $users,
        private ProductRepository $products,
        private PaymentGateway $gateway,
        private Mailer $mailer
    ) {}
}

// Automatic resolution
$orderService = app(OrderService::class);
// All dependencies automatically injected
```

## Resolving from Container

### app() Helper

```php
// Resolve service
$cache = app(Cache::class);

// Resolve with parameters
$service = app(UserService::class, ['param' => 'value']);
```

### make() Method

```php
$cache = app()->make(Cache::class);
```

### resolve() Helper

```php
$cache = resolve(Cache::class);
```

### Dependency Injection

```php
public function __construct(Cache $cache)
{
    // Automatically resolved
}
```

## Complete Examples

### Repository Pattern

```php
<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function all(): array;
    public function create(array $data): User;
    public function update(int $id, array $data): User;
    public function delete(int $id): bool;
}

class EloquentUserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?User
    {
        return User::find($id);
    }
    
    public function all(): array
    {
        return User::all()->toArray();
    }
    
    public function create(array $data): User
    {
        return User::create($data);
    }
    
    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }
    
    public function delete(int $id): bool
    {
        return User::destroy($id) > 0;
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

// Service
class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private Cache $cache
    ) {}
    
    public function getUser(int $id): ?User
    {
        return $this->cache->remember("user.{$id}", 3600, function() use ($id) {
            return $this->repository->find($id);
        });
    }
}

// Controller
class UserController extends Controller
{
    public function __construct(
        private UserService $service
    ) {}
    
    public function show(int $id)
    {
        $user = $this->service->getUser($id);
        return $this->json($user);
    }
}
```

### Service Layer

```php
<?php

namespace App\Services;

use App\Repositories\{OrderRepository, ProductRepository, UserRepository};
use App\Mail\OrderConfirmation;
use NeoPhp\Mail\Mailer;

class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private ProductRepository $products,
        private UserRepository $users,
        private PaymentGateway $gateway,
        private Mailer $mailer
    ) {}
    
    public function placeOrder(int $userId, array $items): Order
    {
        // Get user
        $user = $this->users->find($userId);
        
        // Calculate total
        $total = $this->calculateTotal($items);
        
        // Process payment
        $payment = $this->gateway->charge($user, $total);
        
        // Create order
        $order = $this->orders->create([
            'user_id' => $userId,
            'total' => $total,
            'payment_id' => $payment->id,
            'items' => $items
        ]);
        
        // Update inventory
        $this->updateInventory($items);
        
        // Send confirmation
        $this->mailer->to($user)->send(new OrderConfirmation($order));
        
        return $order;
    }
    
    private function calculateTotal(array $items): float
    {
        $total = 0;
        
        foreach ($items as $item) {
            $product = $this->products->find($item['product_id']);
            $total += $product->price * $item['quantity'];
        }
        
        return $total;
    }
    
    private function updateInventory(array $items): void
    {
        foreach ($items as $item) {
            $product = $this->products->find($item['product_id']);
            $product->decrement('stock', $item['quantity']);
        }
    }
}
```

### API Client

```php
<?php

namespace App\Services;

use NeoPhp\Http\Client;
use NeoPhp\Cache\Cache;

class WeatherService
{
    public function __construct(
        private Client $http,
        private Cache $cache,
        private string $apiKey
    ) {}
    
    public function getCurrentWeather(string $city): array
    {
        $cacheKey = "weather.{$city}";
        
        return $this->cache->remember($cacheKey, 1800, function() use ($city) {
            $response = $this->http->get('https://api.weather.com/current', [
                'query' => [
                    'city' => $city,
                    'key' => $this->apiKey
                ]
            ]);
            
            return $response->json();
        });
    }
}

// Service Provider
class WeatherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WeatherService::class, function($app) {
            return new WeatherService(
                $app->make(Client::class),
                $app->make(Cache::class),
                $app->config->get('weather.api_key')
            );
        });
    }
}
```

## Advanced Patterns

### Factory Pattern

```php
interface NotificationInterface
{
    public function send(string $message): void;
}

class EmailNotification implements NotificationInterface
{
    public function send(string $message): void
    {
        // Send email
    }
}

class SmsNotification implements NotificationInterface
{
    public function send(string $message): void
    {
        // Send SMS
    }
}

class NotificationFactory
{
    public function __construct(
        private Container $container
    ) {}
    
    public function create(string $type): NotificationInterface
    {
        return match($type) {
            'email' => $this->container->make(EmailNotification::class),
            'sms' => $this->container->make(SmsNotification::class),
            default => throw new InvalidArgumentException("Unknown type: {$type}")
        };
    }
}

// Usage
$factory = app(NotificationFactory::class);
$notification = $factory->create('email');
$notification->send('Hello!');
```

### Decorator Pattern

```php
interface CacheInterface
{
    public function get(string $key);
    public function put(string $key, $value, int $ttl);
}

class RedisCache implements CacheInterface
{
    public function get(string $key)
    {
        return $this->redis->get($key);
    }
    
    public function put(string $key, $value, int $ttl)
    {
        $this->redis->setex($key, $ttl, $value);
    }
}

class LoggingCacheDecorator implements CacheInterface
{
    public function __construct(
        private CacheInterface $cache,
        private Logger $logger
    ) {}
    
    public function get(string $key)
    {
        $this->logger->info("Cache get: {$key}");
        return $this->cache->get($key);
    }
    
    public function put(string $key, $value, int $ttl)
    {
        $this->logger->info("Cache put: {$key}");
        $this->cache->put($key, $value, $ttl);
    }
}

// Service Provider
public function register(): void
{
    $this->app->singleton(CacheInterface::class, function($app) {
        $redis = new RedisCache($app['redis']);
        return new LoggingCacheDecorator($redis, $app['logger']);
    });
}
```

## Testing with Dependency Injection

### Mocking Dependencies

```php
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testGetUser()
    {
        // Mock repository
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('find')
            ->with(1)
            ->willReturn(new User(['id' => 1, 'name' => 'John']));
        
        // Mock cache
        $cache = $this->createMock(Cache::class);
        $cache->method('remember')
            ->willReturnCallback(function($key, $ttl, $callback) {
                return $callback();
            });
        
        // Create service with mocks
        $service = new UserService($repository, $cache);
        
        // Test
        $user = $service->getUser(1);
        $this->assertEquals('John', $user->name);
    }
}
```

## Best Practices

### 1. Program to Interfaces

```php
// Good ✅
public function __construct(UserRepositoryInterface $repository) {}

// Bad ❌
public function __construct(EloquentUserRepository $repository) {}
```

### 2. Constructor Injection for Required Dependencies

```php
// Good ✅
public function __construct(
    private Database $db,  // Required
    private Cache $cache   // Required
) {}

// Bad ❌
public function setDatabase(Database $db) {}  // Optional
```

### 3. Avoid Service Locator Pattern

```php
// Good ✅
public function __construct(UserService $service) {}

// Bad ❌
public function index() {
    $service = app(UserService::class);  // Service locator
}
```

### 4. Use Type Hints

```php
// Good ✅
public function __construct(Cache $cache) {}

// Bad ❌
public function __construct($cache) {}  // No type
```

### 5. Keep Constructors Simple

```php
// Good ✅
public function __construct(UserRepository $repository) {
    $this->repository = $repository;
}

// Bad ❌
public function __construct(UserRepository $repository) {
    $this->repository = $repository;
    $this->loadConfiguration();  // Logic in constructor
    $this->initializeCache();
}
```

## Next Steps

- [Service Providers](introduction.md)
- [Container](container.md)
- [Testing](../testing/introduction.md)
- [Architecture](../core/foundation-architecture.md)
