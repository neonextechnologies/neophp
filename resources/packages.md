# Packages & Extensions

Discover packages and extensions that extend NeoPHP functionality.

## Table of Contents

- [Official Packages](#official-packages)
- [Community Packages](#community-packages)
- [Package Development](#package-development)
- [Finding Packages](#finding-packages)

---

## Official Packages

### Core Extensions

**neo/cache-redis**

Advanced Redis caching with cluster support

```bash
composer require neo/cache-redis
```

Features:

- Redis Cluster support
- Sentinel support
- Connection pooling
- Tag-based caching

Docs: [Link to docs]

**neo/queue-advanced**

Advanced queue features

```bash
composer require neo/queue-advanced
```

Features:

- Job chaining
- Batch processing
- Priority queues
- Job middleware

Docs: [Link to docs]

**neo/storage-s3**

Amazon S3 storage driver

```bash
composer require neo/storage-s3
```

Features:

- S3 integration
- CloudFront support
- Multipart uploads
- Signed URLs

Docs: [Link to docs]

### Development Tools

**neo/debugbar**

Development debug toolbar

```bash
composer require neo/debugbar --dev
```

Features:

- SQL query profiling
- Request/response inspection
- Timeline visualization
- Memory usage tracking

Docs: [Link to docs]

**neo/telescope**

Application debugging and monitoring

```bash
composer require neo/telescope --dev
```

Features:

- Request monitoring
- Query profiling
- Job tracking
- Exception tracking

Docs: [Link to docs]

**neo/tinker**

Interactive REPL

```bash
composer require neo/tinker
```

Features:

- Interactive shell
- Code execution
- Model interaction
- Quick debugging

Usage:

```bash
php neo tinker
```

---

## Community Packages

### Authentication & Authorization

**community/auth-socialite**

Social authentication (OAuth)

```bash
composer require community/auth-socialite
```

Providers:

- GitHub
- Google
- Facebook
- Twitter
- LinkedIn

Example:

```php
use Community\Socialite\Facades\Socialite;

Route::get('/auth/github', function() {
    return Socialite::driver('github')->redirect();
});

Route::get('/auth/github/callback', function() {
    $user = Socialite::driver('github')->user();
    // Handle authentication
});
```

**community/permissions**

Advanced role and permission management

```bash
composer require community/permissions
```

Features:

- Role-based access control
- Permission hierarchies
- Team/organization support
- Middleware integration

Example:

```php
use Community\Permissions\Traits\HasRoles;

class User extends Model
{
    use HasRoles;
}

$user->assignRole('admin');
$user->givePermissionTo('edit posts');
```

### API Development

**community/api-resources**

Advanced API resource transformers

```bash
composer require community/api-resources
```

Features:

- Resource transformers
- Conditional fields
- Nested resources
- Pagination support

Example:

```php
use Community\ApiResources\Resource;

class UserResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($request->user()->isAdmin(), $this->email),
            'posts' => PostResource::collection($this->whenLoaded('posts'))
        ];
    }
}
```

**community/api-versioning**

API versioning support

```bash
composer require community/api-versioning
```

Features:

- URL versioning
- Header versioning
- Deprecation warnings
- Version routing

Example:

```php
Route::apiVersion('v1', function() {
    Route::get('/users', [UserV1Controller::class, 'index']);
});

Route::apiVersion('v2', function() {
    Route::get('/users', [UserV2Controller::class, 'index']);
});
```

### Database & Models

**community/eloquent-uuid**

UUID primary keys for models

```bash
composer require community/eloquent-uuid
```

Features:

- Automatic UUID generation
- UUID validation
- Migration helpers
- Route model binding support

Example:

```php
use Community\Eloquent\Traits\HasUuid;

class Post extends Model
{
    use HasUuid;
    
    protected $keyType = 'string';
    public $incrementing = false;
}
```

**community/model-caching**

Automatic model caching

```bash
composer require community/model-caching
```

Features:

- Automatic cache invalidation
- Relationship caching
- Query caching
- Configurable TTL

Example:

```php
use Community\ModelCaching\Traits\Cachable;

class Post extends Model
{
    use Cachable;
    
    protected $cacheFor = 3600;
}
```

### Testing

**community/pest**

Elegant testing framework

```bash
composer require community/pest --dev
```

Features:

- Beautiful syntax
- Snapshot testing
- Parallel execution
- Rich assertions

Example:

```php
test('user can register', function() {
    $response = $this->post('/register', [
        'name' => 'John',
        'email' => 'john@example.com'
    ]);
    
    $response->assertRedirect('/dashboard');
    expect(User::count())->toBe(1);
});
```

**community/factory-generator**

Auto-generate model factories

```bash
composer require community/factory-generator --dev
```

Usage:

```bash
php neo generate:factories
```

### Forms & Validation

**community/form-builder**

Declarative form builder

```bash
composer require community/form-builder
```

Example:

```php
use Community\Forms\Form;

$form = Form::make()
    ->text('name', 'Your Name')
        ->required()
        ->maxLength(255)
    ->email('email', 'Email Address')
        ->required()
    ->password('password', 'Password')
        ->required()
        ->minLength(8)
    ->submit('Register');

return view('register', compact('form'));
```

**community/validation-rules**

Additional validation rules

```bash
composer require community/validation-rules
```

Rules:

- `phone` - Phone number validation
- `iban` - IBAN validation
- `credit_card` - Credit card validation
- `slug` - URL slug validation
- `color` - Color code validation

### Media & Files

**community/media-library**

Media management system

```bash
composer require community/media-library
```

Features:

- File uploads
- Image transformations
- Responsive images
- Media organization

Example:

```php
use Community\MediaLibrary\HasMedia;

class Post extends Model
{
    use HasMedia;
}

$post->addMedia($request->file('image'))
    ->toMediaCollection('images');

$post->getFirstMediaUrl('images', 'thumb');
```

**community/image-optimizer**

Automatic image optimization

```bash
composer require community/image-optimizer
```

Features:

- Multiple optimization engines
- Automatic optimization
- Size reduction
- Format conversion

### Search

**community/search**

Full-text search integration

```bash
composer require community/search
```

Engines:

- Elasticsearch
- Algolia
- Meilisearch
- Database

Example:

```php
use Community\Search\Searchable;

class Post extends Model
{
    use Searchable;
    
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'author' => $this->author->name
        ];
    }
}

$posts = Post::search('laravel')->get();
```

### Payment Integration

**community/payments**

Multi-gateway payment processing

```bash
composer require community/payments
```

Gateways:

- Stripe
- PayPal
- Braintree
- Square

Example:

```php
use Community\Payments\Facades\Payment;

$charge = Payment::charge([
    'amount' => 1000,
    'currency' => 'usd',
    'source' => $token,
    'description' => 'Order payment'
]);
```

### Notifications

**community/notification-channels**

Additional notification channels

```bash
composer require community/notification-channels
```

Channels:

- Slack
- Telegram
- Discord
- SMS (Twilio, Nexmo)
- Push notifications

Example:

```php
public function via($notifiable)
{
    return ['mail', 'slack', 'telegram'];
}
```

---

## Package Development

### Creating a Package

**Structure:**

```
my-package/
├── src/
│   ├── MyPackageServiceProvider.php
│   └── ...
├── tests/
├── composer.json
└── README.md
```

**composer.json:**

```json
{
    "name": "vendor/my-package",
    "description": "Package description",
    "type": "library",
    "require": {
        "php": "^8.0",
        "neo/framework": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\MyPackage\\": "src/"
        }
    },
    "extra": {
        "neo": {
            "providers": [
                "Vendor\\MyPackage\\MyPackageServiceProvider"
            ]
        }
    }
}
```

**Service Provider:**

```php
<?php

namespace Vendor\MyPackage;

use Neo\Support\ServiceProvider;

class MyPackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('mypackage', function() {
            return new MyPackage();
        });
    }
    
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/mypackage.php' => config_path('mypackage.php'),
        ], 'config');
        
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mypackage');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
```

### Package Discovery

Packages are auto-discovered via composer.json `extra.neo.providers`.

### Testing Packages

```php
<?php

namespace Vendor\MyPackage\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Vendor\MyPackage\MyPackageServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [MyPackageServiceProvider::class];
    }
}
```

### Publishing Packages

1. **Publish to Packagist:**
   - Create account on packagist.org
   - Submit package
   - Configure GitHub webhook

2. **Documentation:**
   - Write comprehensive README
   - Add usage examples
   - Document configuration options

3. **Testing:**
   - Write thorough tests
   - Set up CI/CD
   - Maintain compatibility

---

## Finding Packages

### Official Registry

**NeoPHP Packages:** https://packages.neophp.dev

- Search packages
- Browse categories
- View popularity
- Read documentation

### Packagist

Search with tag `neophp`:

https://packagist.org/?tags=neophp

### GitHub

Search repositories:

- Topic: `neophp`
- Topic: `neophp-package`

---

## Package Guidelines

### Best Practices

**Naming:**

- Use vendor prefix: `vendor/package-name`
- Descriptive names
- Lowercase with hyphens

**Documentation:**

- Clear README
- Usage examples
- Configuration options
- Changelog

**Testing:**

- Unit tests
- Integration tests
- CI/CD setup
- Code coverage

**Compatibility:**

- Follow SemVer
- Support LTS versions
- Document requirements
- Migration guides

### Publishing Checklist

- [ ] Comprehensive README
- [ ] Usage examples
- [ ] Tests with good coverage
- [ ] CI/CD configured
- [ ] License file (MIT recommended)
- [ ] CHANGELOG.md
- [ ] Tagged release
- [ ] Published to Packagist

---

## Recommended Packages

### Must-Have Development

- `neo/debugbar` - Debug toolbar
- `neo/telescope` - Application insights
- `neo/tinker` - REPL shell
- `community/pest` - Testing framework

### Production Essentials

- `neo/cache-redis` - Redis caching
- `community/search` - Full-text search
- `community/media-library` - Media management
- `community/monitoring` - Application monitoring

### API Development

- `community/api-resources` - Resource transformers
- `community/api-versioning` - Version management
- `community/api-docs` - Auto documentation
- `community/rate-limiter` - Advanced rate limiting

---

## Need a Package?

Can't find what you need?

1. **Search thoroughly** - Check all sources
2. **Ask community** - Discord, forums
3. **Request feature** - GitHub discussions
4. **Build it yourself** - Follow package guide
5. **Share with community** - Publish on Packagist

---

**Explore packages:** https://packages.neophp.dev 📦
