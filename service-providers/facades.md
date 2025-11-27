# Facades

Facades provide a static interface to classes available in the application container.

## What is a Facade?

A facade is a class that provides static access to an object from the container:

```php
// Without facade
$cache = app(Cache::class);
$cache->put('key', 'value', 3600);

// With facade
Cache::put('key', 'value', 3600);
```

## How Facades Work

Facades use PHP's `__callStatic` magic method:

```php
Cache::get('key');

// Internally:
// 1. Resolve 'cache' from container
// 2. Call get() method on resolved instance
```

## Built-in Facades

### Cache

```php
use NeoPhp\Cache\Facades\Cache;

// Store
Cache::put('key', 'value', 3600);

// Retrieve
$value = Cache::get('key');

// Remember
$users = Cache::remember('users', 3600, function() {
    return User::all();
});

// Forget
Cache::forget('key');

// Flush
Cache::flush();
```

### DB

```php
use NeoPhp\Database\Facades\DB;

// Query
$users = DB::table('users')->get();

// Raw query
$users = DB::select('SELECT * FROM users WHERE active = ?', [1]);

// Insert
DB::table('users')->insert([
    'name' => 'John',
    'email' => 'john@example.com'
]);

// Transaction
DB::transaction(function() {
    DB::table('users')->insert(['name' => 'John']);
    DB::table('profiles')->insert(['user_id' => 1]);
});
```

### Config

```php
use NeoPhp\Config\Facades\Config;

// Get value
$appName = Config::get('app.name');

// Get with default
$debug = Config::get('app.debug', false);

// Set value
Config::set('app.timezone', 'UTC');

// Check if exists
if (Config::has('database.connections.mysql')) {
    // ...
}
```

### Log

```php
use NeoPhp\Log\Facades\Log;

// Info
Log::info('User logged in', ['user_id' => 1]);

// Error
Log::error('Payment failed', ['order_id' => 123]);

// Warning
Log::warning('Low stock', ['product_id' => 456]);

// Debug
Log::debug('Processing order', ['data' => $orderData]);
```

### Mail

```php
use NeoPhp\Mail\Facades\Mail;

// Send mail
Mail::to('user@example.com')
    ->send(new WelcomeEmail($user));

// Send to multiple
Mail::to(['user1@example.com', 'user2@example.com'])
    ->cc('admin@example.com')
    ->send(new Newsletter($content));

// Queue mail
Mail::to('user@example.com')
    ->queue(new OrderConfirmation($order));
```

### Storage

```php
use NeoPhp\Storage\Facades\Storage;

// Put file
Storage::put('path/to/file.txt', 'contents');

// Get file
$contents = Storage::get('path/to/file.txt');

// Delete
Storage::delete('path/to/file.txt');

// Check exists
if (Storage::exists('path/to/file.txt')) {
    // ...
}

// Download
return Storage::download('path/to/file.pdf');
```

### Route

```php
use NeoPhp\Routing\Facades\Route;

// Define routes
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);

// Route groups
Route::prefix('api')->group(function() {
    Route::get('/users', [ApiUserController::class, 'index']);
});

// Named routes
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

// Generate URL
$url = Route::route('profile');
```

### Validator

```php
use NeoPhp\Validation\Facades\Validator;

// Validate data
$validator = Validator::make($data, [
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:users'],
    'age' => ['required', 'integer', 'min:18'],
]);

if ($validator->fails()) {
    return $validator->errors();
}

// Validate from model
$rules = Validator::fromModel(User::class);
```

## Creating Custom Facades

### 1. Create Service Class

```php
<?php

namespace App\Services;

class PaymentService
{
    public function charge(float $amount): bool
    {
        // Process payment
        return true;
    }
    
    public function refund(string $transactionId): bool
    {
        // Process refund
        return true;
    }
}
```

### 2. Register in Container

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use App\Services\PaymentService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('payment', function($app) {
            return new PaymentService(
                $app['config']['payment']
            );
        });
    }
}
```

### 3. Create Facade Class

```php
<?php

namespace App\Facades;

use NeoPhp\Foundation\Facade;

/**
 * @method static bool charge(float $amount)
 * @method static bool refund(string $transactionId)
 * 
 * @see \App\Services\PaymentService
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'payment';
    }
}
```

### 4. Use Facade

```php
use App\Facades\Payment;

// Charge
$success = Payment::charge(99.99);

// Refund
$refunded = Payment::refund('txn_123');
```

## Real-Time Facades

Convert any class to facade on-the-fly:

```php
namespace App\Services;

class Analytics
{
    public function track(string $event, array $data): void
    {
        // Track analytics
    }
}

// Use as facade by importing Facades namespace
use Facades\App\Services\Analytics;

// Now use statically
Analytics::track('user.registered', ['user_id' => 1]);
```

## Complete Examples

### Notification Facade

```php
<?php

namespace App\Services;

class NotificationService
{
    public function __construct(
        private Mailer $mailer,
        private SmsClient $sms,
        private PushService $push
    ) {}
    
    public function send(string $channel, string $message, array $recipients): void
    {
        match($channel) {
            'email' => $this->sendEmail($message, $recipients),
            'sms' => $this->sendSms($message, $recipients),
            'push' => $this->sendPush($message, $recipients),
            default => throw new InvalidArgumentException("Unknown channel: {$channel}")
        };
    }
    
    public function sendEmail(string $message, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            $this->mailer->to($recipient)->send($message);
        }
    }
    
    public function sendSms(string $message, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            $this->sms->send($recipient, $message);
        }
    }
    
    public function sendPush(string $message, array $recipients): void
    {
        $this->push->sendToDevices($recipients, $message);
    }
}

// Service Provider
class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('notification', NotificationService::class);
    }
}

// Facade
namespace App\Facades;

use NeoPhp\Foundation\Facade;

/**
 * @method static void send(string $channel, string $message, array $recipients)
 * @method static void sendEmail(string $message, array $recipients)
 * @method static void sendSms(string $message, array $recipients)
 * @method static void sendPush(string $message, array $recipients)
 */
class Notification extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'notification';
    }
}

// Usage
use App\Facades\Notification;

Notification::send('email', 'Welcome!', ['user@example.com']);
Notification::sendSms('Code: 1234', ['+1234567890']);
```

### Analytics Facade

```php
<?php

namespace App\Services;

class AnalyticsService
{
    public function __construct(
        private Database $db,
        private Cache $cache
    ) {}
    
    public function track(string $event, array $data = []): void
    {
        $this->db->table('analytics_events')->insert([
            'event' => $event,
            'data' => json_encode($data),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
    
    public function getStats(string $event, string $period = 'day'): array
    {
        $cacheKey = "analytics.{$event}.{$period}";
        
        return $this->cache->remember($cacheKey, 3600, function() use ($event, $period) {
            return $this->db->table('analytics_events')
                ->where('event', $event)
                ->where('created_at', '>=', $this->getPeriodStart($period))
                ->groupBy($this->getGroupBy($period))
                ->selectRaw('COUNT(*) as count, DATE(created_at) as date')
                ->get()
                ->toArray();
        });
    }
    
    public function pageView(string $url): void
    {
        $this->track('page.view', ['url' => $url]);
    }
    
    public function conversion(string $type, float $value): void
    {
        $this->track('conversion', [
            'type' => $type,
            'value' => $value
        ]);
    }
}

// Facade
namespace App\Facades;

/**
 * @method static void track(string $event, array $data = [])
 * @method static array getStats(string $event, string $period = 'day')
 * @method static void pageView(string $url)
 * @method static void conversion(string $type, float $value)
 */
class Analytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'analytics';
    }
}

// Usage
use App\Facades\Analytics;

Analytics::track('user.registered', ['plan' => 'premium']);
Analytics::pageView('/pricing');
Analytics::conversion('sale', 99.99);

$stats = Analytics::getStats('user.registered', 'week');
```

## Facade Testing

### Mocking Facades

```php
use NeoPhp\Cache\Facades\Cache;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testGetUser()
    {
        // Mock facade
        Cache::shouldReceive('remember')
            ->once()
            ->with('user.1', 3600, \Closure::class)
            ->andReturn(new User(['id' => 1, 'name' => 'John']));
        
        $service = new UserService();
        $user = $service->getUser(1);
        
        $this->assertEquals('John', $user->name);
    }
    
    public function testCacheMiss()
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('user.999')
            ->andReturn(null);
        
        $service = new UserService();
        $user = $service->getUser(999);
        
        $this->assertNull($user);
    }
}
```

### Spy Facades

```php
Cache::spy();

// Code that uses Cache
$service->getUsers();

// Assert Cache was called
Cache::shouldHaveReceived('get')->once();
Cache::shouldHaveReceived('put')->with('users', \Mockery::any(), 3600);
```

## Facade vs Dependency Injection

### Use Facades When:

- Quick prototyping
- Simple operations
- Testing with mocks
- Static helper methods

```php
// Good for simple operations
Cache::put('key', 'value', 3600);
Log::info('User logged in');
```

### Use Dependency Injection When:

- Complex dependencies
- Need to swap implementations
- Testing with actual instances
- Better IDE support

```php
// Good for complex services
class UserService
{
    public function __construct(
        private UserRepository $repository,
        private Cache $cache,
        private Validator $validator
    ) {}
}
```

## Best Practices

### 1. Document Facade Methods

```php
/**
 * @method static bool charge(float $amount)
 * @method static bool refund(string $transactionId)
 * @method static array getTransactions()
 * 
 * @see \App\Services\PaymentService
 */
class Payment extends Facade
```

### 2. Use Meaningful Names

```php
// Good ✅
class Payment extends Facade
class Notification extends Facade

// Bad ❌
class Pay extends Facade
class Notif extends Facade
```

### 3. Keep Facades Simple

```php
// Good ✅
Cache::get('key');
Cache::put('key', 'value', 3600);

// Bad ❌ - Too complex for facade
Cache::rememberWithTags(['users', 'active'], 'active_users', 3600, function() {
    return User::where('active', true)->get();
});
```

### 4. Prefer Dependency Injection for Services

```php
// Good ✅ for service classes
class OrderService
{
    public function __construct(
        private PaymentGateway $payment,
        private Mailer $mailer
    ) {}
}

// Facades good for simple helpers
Cache::put('key', 'value', 3600);
```

## Next Steps

- [Service Providers](introduction.md)
- [Dependency Injection](dependency-injection.md)
- [Container](container.md)
- [Testing](../testing/introduction.md)
