# Foundation Architecture

NeoPhp is built on a foundation architecture that emphasizes flexibility, modularity, and clean code principles.

## What is Foundation Architecture?

Unlike traditional full-stack frameworks that come with everything built-in, NeoPhp provides a **foundation** - a set of core building blocks that you can use to build your own framework or application.

Think of it as the difference between:

### Traditional Framework
```
Laravel/Symfony
└─ Everything included
   ├─ Router
   ├─ ORM
   ├─ Auth
   ├─ Queue
   ├─ Cache
   └─ ... (Monolithic)
```

### Foundation Framework
```
NeoPhp
└─ Core building blocks
   ├─ Contracts (Interfaces)
   ├─ Service Providers
   ├─ Plugin System
   ├─ Metadata System
   └─ CLI Tools
      └─ Build what you need on top
```

## Core Principles

### 1. Contract-First Design

Everything starts with interfaces (contracts). This means:

✅ **Flexibility** - Swap implementations easily
✅ **Testability** - Mock interfaces for testing
✅ **No Lock-in** - Not tied to specific implementations

```php
// Define behavior through interface
interface DatabaseInterface {
    public function query(string $sql, array $params = []): array;
}

// Your code depends on interface, not implementation
class UserRepository {
    public function __construct(private DatabaseInterface $db) {}
}

// Easily swap implementations
$repo = new UserRepository(new MySQLDatabase());
// or
$repo = new UserRepository(new PostgreSQLDatabase());
// or
$repo = new UserRepository(new MockDatabase()); // for testing
```

### 2. Service Provider Pattern

Organize your code into modular, reusable services:

```php
class PaymentServiceProvider extends ServiceProvider
{
    // Register services
    public function register(): void {
        $this->app->singleton('payment', fn() => new StripePayment());
    }
    
    // Bootstrap services
    public function boot(): void {
        // Load config, routes, etc.
    }
}
```

**Benefits:**
- **Modular** - Each provider handles one concern
- **Reusable** - Share providers across projects
- **Lazy Loading** - Load only when needed
- **Organized** - Clear separation of concerns

### 3. Plugin Architecture

Extend functionality without modifying core code:

```php
class BlogPlugin extends Plugin
{
    public function install(): void {
        // Create tables, copy files
    }
    
    public function boot(): void {
        // Register routes, hooks, services
    }
    
    public function uninstall(): void {
        // Cleanup
    }
}
```

**Benefits:**
- **Extensible** - Add features via plugins
- **Maintainable** - Core stays clean
- **Distributable** - Share plugins easily
- **Isolate** - Plugin code is self-contained

### 4. Metadata-Driven Development

Define models declaratively using PHP 8 attributes:

```php
#[Table('products')]
class Product
{
    #[Field(type: 'string', validation: ['required'])]
    public string $name;
    
    #[Field(type: 'decimal', validation: ['required', 'min:0'])]
    public float $price;
}

// Auto-generate everything
$form = form()->make(Product::class);
$rules = metadata()->getValidationRules(Product::class);
```

**Benefits:**
- **DRY** - Define once, use everywhere
- **Consistent** - Single source of truth
- **Auto-generate** - Forms, validation, docs
- **Type-safe** - PHP 8 attributes

### 5. Hook System

Event-driven architecture with WordPress-style hooks:

```php
// Register hook
HookManager::addAction('user.created', function($user) {
    Mail::send($user->email, 'Welcome!');
});

// Trigger hook
HookManager::doAction('user.created', $user);

// Filter data
$name = HookManager::applyFilters('user.name', $name);
```

**Benefits:**
- **Decoupled** - Components don't need to know about each other
- **Extensible** - Plugins can hook into anything
- **Flexible** - Add functionality without changing code

## Architecture Layers

```
┌─────────────────────────────────────┐
│     Your Application Layer          │
│  (Controllers, Models, Views)       │
├─────────────────────────────────────┤
│     Plugin Layer                    │
│  (Blog, Shop, Custom Features)      │
├─────────────────────────────────────┤
│     Service Provider Layer          │
│  (Database, Cache, Mail, etc.)      │
├─────────────────────────────────────┤
│     Foundation Layer                │
│  (Contracts, Metadata, Hooks)       │
├─────────────────────────────────────┤
│     PHP 8.0+                        │
└─────────────────────────────────────┘
```

## 10 Core Contracts

NeoPhp provides 10 pure interfaces:

1. **DatabaseInterface** - Database operations
2. **CacheInterface** - Caching operations
3. **QueueInterface** - Queue operations
4. **LoggerInterface** - Logging operations
5. **StorageInterface** - File storage
6. **MailerInterface** - Email sending
7. **ValidatorInterface** - Data validation
8. **ServiceProviderInterface** - Provider contract
9. **PluginInterface** - Plugin contract
10. **MetadataInterface** - Metadata operations

## Benefits of Foundation Architecture

### For Developers

✅ **Learn Once, Use Anywhere** - Same patterns across projects
✅ **No Bloat** - Only include what you need
✅ **Full Control** - Build exactly what you want
✅ **Easy Testing** - Interface-based design
✅ **Fast Development** - CLI tools and generators

### For Projects

✅ **Flexible** - Adapt to any requirement
✅ **Scalable** - Add features via plugins
✅ **Maintainable** - Clean, organized code
✅ **Portable** - Not locked to specific implementations
✅ **Future-proof** - Swap components easily

### For Teams

✅ **Clear Structure** - Everyone knows where things go
✅ **Parallel Development** - Work on different plugins
✅ **Code Reuse** - Share providers and plugins
✅ **Standards** - Consistent code patterns

## When to Use NeoPhp

### ✅ Perfect For:

- Building custom frameworks
- Metadata-driven CRUD applications
- Plugin-based architectures
- API services
- Admin panels
- Multi-tenant SaaS
- Modular applications

### ❌ Not Ideal For:

- Simple static websites (use flat-file CMS)
- Prototypes (use full framework like Laravel)
- When you need everything now (NeoPhp is a foundation)

## Comparison with Other Frameworks

### vs Laravel/Symfony (Full Framework)
- **Laravel**: Everything included, opinionated
- **NeoPhp**: Foundation only, build what you need

### vs Micro Frameworks (Slim, Lumen)
- **Slim**: Minimal routing and middleware
- **NeoPhp**: Full foundation with CLI, metadata, plugins

### vs Neonex Core
- **Neonex Core**: Another foundation framework
- **NeoPhp**: Similar philosophy, metadata-driven

## Next Steps

- [Contracts & Interfaces](contracts.md)
- [Service Providers](service-providers.md)
- [Plugin System](plugins.md)
- [Metadata System](metadata.md)
