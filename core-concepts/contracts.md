# Contracts & Interfaces

Contracts (interfaces) are the foundation of NeoPhp's architecture. They define what a component should do without specifying how it does it.

## Why Contracts?

Contracts provide several key benefits:

### 1. Flexibility
Swap implementations without changing your code:

```php
// Your code depends on the interface
class UserService {
    public function __construct(private DatabaseInterface $db) {}
}

// Use MySQL
$service = new UserService(new MySQLDatabase());

// Switch to PostgreSQL - no code changes needed
$service = new UserService(new PostgreSQLDatabase());
```

### 2. Testability
Easy to mock for testing:

```php
class MockDatabase implements DatabaseInterface {
    public function query(string $sql, array $params = []): array {
        return ['id' => 1, 'name' => 'Test'];
    }
}

// Test with mock
$service = new UserService(new MockDatabase());
```

### 3. Clear APIs
Interfaces document what methods are available:

```php
interface CacheInterface {
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
}
```

## Available Contracts

NeoPhp provides 10 core contracts:

### DatabaseInterface

Database operations:

```php
interface DatabaseInterface
{
    public function connect(): void;
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): bool;
    public function lastInsertId(): int;
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;
}
```

**Usage:**
```php
class ProductRepository {
    public function __construct(private DatabaseInterface $db) {}
    
    public function find(int $id): ?array {
        return $this->db->query(
            'SELECT * FROM products WHERE id = ?',
            [$id]
        )[0] ?? null;
    }
}
```

### CacheInterface

Caching operations:

```php
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): bool;
    public function has(string $key): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function remember(string $key, callable $callback, int $ttl = 3600): mixed;
}
```

**Usage:**
```php
class UserService {
    public function __construct(
        private DatabaseInterface $db,
        private CacheInterface $cache
    ) {}
    
    public function find(int $id): ?array {
        return $this->cache->remember("user.$id", function() use ($id) {
            return $this->db->query('SELECT * FROM users WHERE id = ?', [$id])[0];
        });
    }
}
```

### QueueInterface

Queue operations:

```php
interface QueueInterface
{
    public function push(string $job, array $data = []): bool;
    public function later(int $delay, string $job, array $data = []): bool;
    public function pop(string $queue = 'default'): ?array;
    public function size(string $queue = 'default'): int;
    public function clear(string $queue = 'default'): bool;
}
```

**Usage:**
```php
class EmailService {
    public function __construct(private QueueInterface $queue) {}
    
    public function sendWelcomeEmail(User $user): void {
        $this->queue->push('SendWelcomeEmail', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }
}
```

### LoggerInterface

Logging operations (PSR-3 compatible):

```php
interface LoggerInterface
{
    public function emergency(string $message, array $context = []): void;
    public function alert(string $message, array $context = []): void;
    public function critical(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
    public function notice(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
    public function debug(string $message, array $context = []): void;
    public function log(string $level, string $message, array $context = []): void;
}
```

**Usage:**
```php
class OrderService {
    public function __construct(private LoggerInterface $logger) {}
    
    public function create(array $data): Order {
        $this->logger->info('Creating order', ['data' => $data]);
        
        try {
            $order = Order::create($data);
            $this->logger->info('Order created', ['order_id' => $order->id]);
            return $order;
        } catch (\Exception $e) {
            $this->logger->error('Order creation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

### StorageInterface

File storage operations:

```php
interface StorageInterface
{
    public function put(string $path, string $contents): bool;
    public function get(string $path): string;
    public function exists(string $path): bool;
    public function delete(string $path): bool;
    public function copy(string $from, string $to): bool;
    public function move(string $from, string $to): bool;
    public function size(string $path): int;
    public function lastModified(string $path): int;
    public function url(string $path): string;
}
```

**Usage:**
```php
class FileUploadService {
    public function __construct(private StorageInterface $storage) {}
    
    public function upload(UploadedFile $file, string $path): string {
        $filename = uniqid() . '.' . $file->getExtension();
        $fullPath = $path . '/' . $filename;
        
        $this->storage->put($fullPath, $file->getContents());
        
        return $this->storage->url($fullPath);
    }
}
```

### MailerInterface

Email operations:

```php
interface MailerInterface
{
    public function send(string $to, string $subject, string $body): bool;
    public function sendHtml(string $to, string $subject, string $html): bool;
    public function sendTemplate(string $to, string $subject, string $template, array $data): bool;
    public function setFrom(string $email, string $name = ''): self;
    public function addCc(string $email): self;
    public function addBcc(string $email): self;
    public function addAttachment(string $path): self;
}
```

**Usage:**
```php
class NotificationService {
    public function __construct(private MailerInterface $mailer) {}
    
    public function sendWelcome(User $user): void {
        $this->mailer->sendTemplate(
            $user->email,
            'Welcome to ' . config('app.name'),
            'emails.welcome',
            ['user' => $user]
        );
    }
}
```

### ValidatorInterface

Validation operations:

```php
interface ValidatorInterface
{
    public function validate(array $data, array $rules): array;
    public function fails(): bool;
    public function passes(): bool;
    public function errors(): array;
    public function validated(): array;
}
```

**Usage:**
```php
class ProductController {
    public function __construct(private ValidatorInterface $validator) {}
    
    public function store(Request $request): Response {
        $validated = $this->validator->validate($request->all(), [
            'name' => 'required|min:3|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id'
        ]);
        
        if ($this->validator->fails()) {
            return response()->json(['errors' => $this->validator->errors()], 422);
        }
        
        $product = Product::create($validated);
        return response()->json($product, 201);
    }
}
```

### ServiceProviderInterface

Service provider contract:

```php
interface ServiceProviderInterface
{
    public function register(): void;
    public function boot(): void;
    public function provides(): array;
    public function isDeferred(): bool;
}
```

### PluginInterface

Plugin contract:

```php
interface PluginInterface
{
    public function getName(): string;
    public function getVersion(): string;
    public function install(): void;
    public function uninstall(): void;
    public function boot(): void;
    public function isActive(): bool;
}
```

### MetadataInterface

Metadata operations:

```php
interface MetadataInterface
{
    public function getModelMetadata(string $class): array;
    public function getTableName(string $class): string;
    public function getFields(string $class): array;
    public function getRelationships(string $class): array;
    public function getValidationRules(string $class): array;
}
```

## Creating Custom Contracts

You can create your own contracts for domain-specific functionality:

```php
interface PaymentGatewayInterface
{
    public function charge(float $amount, string $currency, array $options): array;
    public function refund(string $transactionId, float $amount): bool;
    public function getBalance(): float;
}

// Implementation for Stripe
class StripePayment implements PaymentGatewayInterface
{
    public function charge(float $amount, string $currency, array $options): array {
        // Stripe implementation
    }
    
    public function refund(string $transactionId, float $amount): bool {
        // Stripe refund
    }
    
    public function getBalance(): float {
        // Get Stripe balance
    }
}

// Implementation for PayPal
class PayPalPayment implements PaymentGatewayInterface
{
    public function charge(float $amount, string $currency, array $options): array {
        // PayPal implementation
    }
    
    public function refund(string $transactionId, float $amount): bool {
        // PayPal refund
    }
    
    public function getBalance(): float {
        // Get PayPal balance
    }
}
```

## Binding Implementations

Bind implementations to contracts using service providers:

```php
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind database
        $this->app->singleton(DatabaseInterface::class, function($app) {
            return new MySQLDatabase(config('database'));
        });
        
        // Bind cache
        $this->app->singleton(CacheInterface::class, function($app) {
            return new FileCache(storage_path('cache'));
        });
        
        // Bind custom contract
        $this->app->singleton(PaymentGatewayInterface::class, function($app) {
            return new StripePayment(config('payment.stripe_key'));
        });
    }
}
```

## Using Contracts with Dependency Injection

NeoPhp automatically resolves dependencies:

```php
// Controller automatically gets the bound implementation
class PaymentController extends Controller
{
    public function __construct(
        private PaymentGatewayInterface $payment,
        private LoggerInterface $logger
    ) {}
    
    public function charge(Request $request): Response
    {
        try {
            $result = $this->payment->charge(
                $request->input('amount'),
                $request->input('currency'),
                $request->input('options', [])
            );
            
            $this->logger->info('Payment successful', ['result' => $result]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            $this->logger->error('Payment failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

## Best Practices

### 1. Always Type-hint Interfaces

```php
// Good ✅
public function __construct(private DatabaseInterface $db) {}

// Bad ❌
public function __construct(private MySQLDatabase $db) {}
```

### 2. Keep Interfaces Small

Follow the Interface Segregation Principle:

```php
// Good ✅
interface Readable {
    public function read(string $key): mixed;
}

interface Writable {
    public function write(string $key, mixed $value): bool;
}

// Bad ❌
interface Storage {
    public function read(string $key): mixed;
    public function write(string $key, mixed $value): bool;
    public function delete(string $key): bool;
    public function list(): array;
    public function sync(): bool;
    public function backup(): bool;
    // Too many responsibilities
}
```

### 3. Document Return Types

```php
interface UserRepositoryInterface
{
    /**
     * Find user by ID
     * 
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User;
    
    /**
     * Get all users
     * 
     * @return User[]
     */
    public function all(): array;
}
```

## Next Steps

- [Service Providers](service-providers.md)
- [Dependency Injection](../advanced/dependency-injection.md)
- [Creating Packages](../packages/creating-packages.md)
