# Caching

Improve application performance with powerful caching capabilities.

## Configuration

Configure cache in `config/cache.php`:

```php
return [
    'default' => env('CACHE_DRIVER', 'redis'),
    
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'prefix' => 'neophp_cache',
        ],
        
        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache'),
        ],
        
        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ],
    ],
];
```

## Basic Usage

### Storing Items

```php
use NeoPhp\Cache\Facades\Cache;

// Store for specific time (seconds)
Cache::put('key', 'value', 3600);

// Store forever
Cache::forever('key', 'value');

// Store if doesn't exist
Cache::add('key', 'value', 3600);
```

### Retrieving Items

```php
// Get value
$value = Cache::get('key');

// Get with default
$value = Cache::get('key', 'default');

// Get and delete
$value = Cache::pull('key');

// Check existence
if (Cache::has('key')) {
    // Key exists
}
```

### Removing Items

```php
// Delete single key
Cache::forget('key');

// Delete multiple keys
Cache::forget(['key1', 'key2', 'key3']);

// Clear all cache
Cache::flush();
```

## Cache Retrieval & Storage

### Remember

Retrieve or store if missing:

```php
$users = Cache::remember('users', 3600, function() {
    return User::all();
});
```

### Remember Forever

```php
$settings = Cache::rememberForever('settings', function() {
    return Setting::all();
});
```

### Increment & Decrement

```php
// Increment
Cache::increment('page_views');
Cache::increment('page_views', 5);

// Decrement
Cache::decrement('stock_count');
Cache::decrement('stock_count', 3);
```

## Cache Tags

Group related cache items:

```php
// Store with tags
Cache::tags(['users', 'premium'])->put('user:1', $user, 3600);

// Retrieve
$user = Cache::tags(['users', 'premium'])->get('user:1');

// Flush tagged items
Cache::tags(['users'])->flush();
Cache::tags(['users', 'premium'])->flush();
```

### Nested Tags

```php
// Store
Cache::tags(['category:1', 'products'])->put('product:1', $product, 3600);
Cache::tags(['category:1', 'products'])->put('product:2', $product, 3600);

// Flush category products
Cache::tags(['category:1'])->flush();
```

## Cache Drivers

### Redis Cache

```php
// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'prefix' => 'app',
],

// .env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Usage:

```php
$cache = Cache::store('redis');
$cache->put('key', 'value', 3600);
```

### File Cache

```php
// config/cache.php
'file' => [
    'driver' => 'file',
    'path' => storage_path('cache'),
],

// .env
CACHE_DRIVER=file
```

### Memcached

```php
// config/cache.php
'memcached' => [
    'driver' => 'memcached',
    'servers' => [
        [
            'host' => env('MEMCACHED_HOST', '127.0.0.1'),
            'port' => env('MEMCACHED_PORT', 11211),
            'weight' => 100,
        ],
    ],
],
```

## Complete Examples

### User Profile Caching

```php
<?php

namespace App\Services;

use App\Models\User;
use NeoPhp\Cache\Facades\Cache;

class UserService
{
    public function getProfile(int $userId): ?array
    {
        return Cache::tags(['users', "user:{$userId}"])
            ->remember("user:profile:{$userId}", 3600, function() use ($userId) {
                $user = User::with(['profile', 'settings'])
                    ->find($userId);
                
                return $user ? $user->toArray() : null;
            });
    }
    
    public function updateProfile(int $userId, array $data): void
    {
        $user = User::findOrFail($userId);
        $user->profile->update($data);
        
        // Invalidate cache
        Cache::tags(["user:{$userId}"])->flush();
    }
}
```

### Product Catalog Caching

```php
<?php

namespace App\Services;

use App\Models\{Product, Category};
use NeoPhp\Cache\Facades\Cache;

class ProductService
{
    public function getProducts(int $categoryId, int $page = 1): array
    {
        $cacheKey = "products:category:{$categoryId}:page:{$page}";
        
        return Cache::tags(['products', "category:{$categoryId}"])
            ->remember($cacheKey, 1800, function() use ($categoryId, $page) {
                return Product::where('category_id', $categoryId)
                    ->where('status', 'active')
                    ->with(['images', 'brand'])
                    ->paginate(20, ['*'], 'page', $page)
                    ->toArray();
            });
    }
    
    public function getFeaturedProducts(): array
    {
        return Cache::tags(['products', 'featured'])
            ->remember('products:featured', 3600, function() {
                return Product::where('is_featured', true)
                    ->where('status', 'active')
                    ->limit(12)
                    ->get()
                    ->toArray();
            });
    }
    
    public function updateProduct(int $productId, array $data): void
    {
        $product = Product::findOrFail($productId);
        $product->update($data);
        
        // Flush product and category cache
        Cache::tags([
            'products',
            "category:{$product->category_id}",
            "product:{$productId}"
        ])->flush();
    }
}
```

### API Response Caching

```php
<?php

namespace App\Services;

use NeoPhp\Http\Client;
use NeoPhp\Cache\Facades\Cache;

class WeatherService
{
    public function __construct(private Client $http) {}
    
    public function getCurrentWeather(string $city): array
    {
        $cacheKey = "weather:{$city}";
        
        return Cache::remember($cacheKey, 1800, function() use ($city) {
            $response = $this->http->get('https://api.weather.com/current', [
                'query' => [
                    'city' => $city,
                    'apikey' => config('services.weather.key')
                ]
            ]);
            
            return $response->json();
        });
    }
    
    public function getForecast(string $city, int $days = 7): array
    {
        $cacheKey = "weather:forecast:{$city}:{$days}";
        
        return Cache::remember($cacheKey, 3600, function() use ($city, $days) {
            $response = $this->http->get('https://api.weather.com/forecast', [
                'query' => [
                    'city' => $city,
                    'days' => $days,
                    'apikey' => config('services.weather.key')
                ]
            ]);
            
            return $response->json();
        });
    }
}
```

### Query Result Caching

```php
<?php

namespace App\Repositories;

use App\Models\Post;
use NeoPhp\Cache\Facades\Cache;

class PostRepository
{
    public function getRecentPosts(int $limit = 10): array
    {
        return Cache::tags(['posts'])
            ->remember("posts:recent:{$limit}", 600, function() use ($limit) {
                return Post::where('status', 'published')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            });
    }
    
    public function getPopularPosts(int $limit = 10): array
    {
        return Cache::tags(['posts', 'popular'])
            ->remember("posts:popular:{$limit}", 3600, function() use ($limit) {
                return Post::where('status', 'published')
                    ->orderBy('view_count', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            });
    }
    
    public function getPostsByTag(string $tag): array
    {
        return Cache::tags(['posts', "tag:{$tag}"])
            ->remember("posts:tag:{$tag}", 1800, function() use ($tag) {
                return Post::whereHas('tags', function($query) use ($tag) {
                    $query->where('slug', $tag);
                })
                ->where('status', 'published')
                ->get()
                ->toArray();
            });
    }
    
    public function createPost(array $data): Post
    {
        $post = Post::create($data);
        
        // Clear posts cache
        Cache::tags(['posts'])->flush();
        
        return $post;
    }
}
```

## Cache Middleware

Cache HTTP responses:

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Http\{Request, Response};
use NeoPhp\Cache\Facades\Cache;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 3600): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }
        
        $cacheKey = 'response:' . md5($request->fullUrl());
        
        // Check cache
        if (Cache::has($cacheKey)) {
            return new Response(Cache::get($cacheKey), 200, [
                'X-Cache' => 'HIT'
            ]);
        }
        
        // Generate response
        $response = $next($request);
        
        // Cache successful responses
        if ($response->status() === 200) {
            Cache::put($cacheKey, $response->content(), $ttl);
        }
        
        return $response->header('X-Cache', 'MISS');
    }
}

// Route with caching
Route::get('/api/products', [ProductController::class, 'index'])
    ->middleware('cache:1800');
```

## Cache Events

Listen to cache events:

```php
<?php

namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Cache\Events\{CacheHit, CacheMissed, KeyWritten, KeyForgotten};

class CacheServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Cache hit
        $this->app['events']->listen(CacheHit::class, function($event) {
            logger()->debug('Cache hit', ['key' => $event->key]);
        });
        
        // Cache miss
        $this->app['events']->listen(CacheMissed::class, function($event) {
            logger()->debug('Cache miss', ['key' => $event->key]);
        });
        
        // Key written
        $this->app['events']->listen(KeyWritten::class, function($event) {
            logger()->info('Cache written', [
                'key' => $event->key,
                'ttl' => $event->ttl
            ]);
        });
        
        // Key forgotten
        $this->app['events']->listen(KeyForgotten::class, function($event) {
            logger()->info('Cache cleared', ['key' => $event->key]);
        });
    }
}
```

## Cache Warming

Pre-populate cache:

```php
<?php

namespace App\Console\Commands;

use NeoPhp\Foundation\Console\Command;
use App\Services\{ProductService, PostService};

class WarmCacheCommand extends Command
{
    protected string $signature = 'cache:warm';
    protected string $description = 'Warm up application cache';
    
    public function handle(
        ProductService $products,
        PostService $posts
    ): int {
        $this->info('Warming cache...');
        
        // Warm product cache
        $this->info('Caching featured products...');
        $products->getFeaturedProducts();
        
        // Warm recent posts
        $this->info('Caching recent posts...');
        $posts->getRecentPosts();
        
        // Warm popular posts
        $this->info('Caching popular posts...');
        $posts->getPopularPosts();
        
        $this->success('Cache warmed successfully');
        
        return 0;
    }
}
```

## Best Practices

### 1. Use Tags for Related Data

```php
// Good ✅
Cache::tags(['users', 'premium'])->put('user:1', $user, 3600);

// Flush all user cache
Cache::tags(['users'])->flush();
```

### 2. Set Appropriate TTL

```php
// Good ✅
Cache::put('users', $users, 3600);        // 1 hour
Cache::put('settings', $settings, 86400); // 1 day
Cache::put('config', $config, 604800);    // 1 week

// Bad ❌
Cache::forever('users', $users);  // May become stale
```

### 3. Invalidate on Updates

```php
// Good ✅
public function updateUser(int $id, array $data): void
{
    $user = User::findOrFail($id);
    $user->update($data);
    
    Cache::tags(["user:{$id}"])->flush();
}
```

### 4. Use Remember Pattern

```php
// Good ✅
$users = Cache::remember('users', 3600, function() {
    return User::all();
});

// Bad ❌
if (!Cache::has('users')) {
    $users = User::all();
    Cache::put('users', $users, 3600);
} else {
    $users = Cache::get('users');
}
```

### 5. Cache Complex Queries

```php
// Good ✅ - Cache expensive query
$stats = Cache::remember('dashboard.stats', 3600, function() {
    return [
        'users' => User::count(),
        'orders' => Order::count(),
        'revenue' => Order::sum('total'),
    ];
});

// Bad ❌ - Query every time
$stats = [
    'users' => User::count(),
    'orders' => Order::count(),
];
```

## Next Steps

- [Queue System](queue.md)
- [Events](events.md)
- [Logging](logging.md)
- [Performance](../guides/performance.md)
