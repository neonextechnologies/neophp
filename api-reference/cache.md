# Cache Classes

Complete reference for caching functionality.

## Cache

Cache manager and facade.

### Store Methods

#### `store($name = null)`

Get cache store instance.

```php
$redis = Cache::store('redis');
$file = Cache::store('file');
```

### Basic Operations

#### `get($key, $default = null)`

Get value from cache.

```php
$value = Cache::get('key');
$value = Cache::get('key', 'default');
$value = Cache::get('key', function() {
    return DB::table('users')->count();
});
```

#### `put($key, $value, $ttl = null)`

Store value in cache.

```php
Cache::put('key', 'value', 3600); // 1 hour
Cache::put('key', 'value', now()->addMinutes(10));
```

#### `add($key, $value, $ttl = null)`

Store if not exists.

```php
if (Cache::add('key', 'value', 3600)) {
    // Added successfully
}
```

#### `forever($key, $value)`

Store indefinitely.

```php
Cache::forever('settings', $settings);
```

#### `has($key)`

Check if key exists.

```php
if (Cache::has('user:123')) {
    // Key exists
}
```

#### `missing($key)`

Check if key doesn't exist.

```php
if (Cache::missing('temp_data')) {
    // Key doesn't exist
}
```

#### `forget($key)`

Remove from cache.

```php
Cache::forget('user:123');
```

#### `flush()`

Clear all cache.

```php
Cache::flush();
```

### Retrieve & Store

#### `remember($key, $ttl, $callback)`

Get or store value.

```php
$users = Cache::remember('users', 3600, function() {
    return DB::table('users')->get();
});
```

#### `rememberForever($key, $callback)`

Get or store forever.

```php
$config = Cache::rememberForever('app.config', function() {
    return Config::all();
});
```

#### `pull($key, $default = null)`

Get and delete.

```php
$value = Cache::pull('temp_key');
```

### Increment & Decrement

#### `increment($key, $value = 1)`

Increment value.

```php
Cache::increment('page_views');
Cache::increment('counter', 5);
```

#### `decrement($key, $value = 1)`

Decrement value.

```php
Cache::decrement('stock');
Cache::decrement('credits', 10);
```

### Tags

#### `tags($names)`

Cache with tags.

```php
Cache::tags(['users', 'admins'])->put('user:123', $user, 3600);

$user = Cache::tags('users')->get('user:123');

Cache::tags(['users'])->flush();
```

### Lock Methods

#### `lock($name, $seconds = 0)`

Acquire cache lock.

```php
$lock = Cache::lock('process:import', 10);

if ($lock->get()) {
    try {
        // Do work
    } finally {
        $lock->release();
    }
}
```

#### `restoreLock($name, $owner)`

Restore lock.

```php
$lock = Cache::restoreLock('process:import', $owner);
```

---

## Repository

Cache repository interface.

### Methods

#### `many($keys)`

Get multiple values.

```php
$values = Cache::many(['key1', 'key2', 'key3']);
```

#### `putMany($values, $ttl = null)`

Store multiple values.

```php
Cache::putMany([
    'key1' => 'value1',
    'key2' => 'value2'
], 3600);
```

#### `getMultiple($keys, $default = null)`

PSR-16 get multiple.

```php
$values = Cache::getMultiple(['key1', 'key2']);
```

#### `setMultiple($values, $ttl = null)`

PSR-16 set multiple.

```php
Cache::setMultiple([
    'key1' => 'value1',
    'key2' => 'value2'
], 3600);
```

#### `deleteMultiple($keys)`

PSR-16 delete multiple.

```php
Cache::deleteMultiple(['key1', 'key2', 'key3']);
```

#### `clear()`

PSR-16 clear all.

```php
Cache::clear();
```

---

## Lock

Cache lock for atomic operations.

### Methods

#### `get($callback = null)`

Acquire lock.

```php
$lock = Cache::lock('import', 10);

if ($lock->get()) {
    // Lock acquired
}

// With callback
$lock->get(function() {
    // Do work
});
```

#### `block($seconds, $callback = null)`

Wait to acquire lock.

```php
$lock->block(5, function() {
    // Lock acquired after waiting
});
```

#### `release()`

Release lock.

```php
$lock->release();
```

#### `owner()`

Get lock owner.

```php
$owner = $lock->owner();
```

#### `forceRelease()`

Force release lock.

```php
Cache::lock('import')->forceRelease();
```

---

## TaggedCache

Tagged cache store.

### Methods

#### `tags($names)`

Specify tags.

```php
$cache = Cache::tags(['users', 'admins']);
```

#### `flush()`

Flush tagged cache.

```php
Cache::tags('users')->flush();
Cache::tags(['users', 'posts'])->flush();
```

---

## Cache Stores

### File Store

File-based cache storage.

```php
// config/cache.php
'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('cache')
    ]
]
```

### Array Store

In-memory cache (request lifetime).

```php
'stores' => [
    'array' => [
        'driver' => 'array'
    ]
]
```

### Redis Store

Redis cache storage.

```php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'prefix' => 'cache:'
    ]
]
```

### Memcached Store

Memcached cache storage.

```php
'stores' => [
    'memcached' => [
        'driver' => 'memcached',
        'servers' => [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 100
            ]
        ]
    ]
]
```

---

## Practical Examples

### User Profile Caching

```php
class UserController
{
    public function show($id)
    {
        $user = Cache::remember("user:{$id}", 3600, function() use ($id) {
            return User::with(['posts', 'profile'])->find($id);
        });
        
        return view('users.show', compact('user'));
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());
        
        // Invalidate cache
        Cache::forget("user:{$id}");
        Cache::tags('users')->flush();
        
        return redirect()->route('users.show', $id);
    }
}
```

### Query Result Caching

```php
class PostRepository
{
    public function getPublished()
    {
        return Cache::remember('posts.published', 600, function() {
            return Post::where('status', 'published')
                ->with('author', 'category')
                ->latest()
                ->get();
        });
    }
    
    public function getByCategory($categoryId)
    {
        $key = "posts.category.{$categoryId}";
        
        return Cache::tags(['posts', 'categories'])->remember($key, 600, function() use ($categoryId) {
            return Post::where('category_id', $categoryId)
                ->published()
                ->get();
        });
    }
    
    public function clearCache()
    {
        Cache::tags(['posts'])->flush();
    }
}
```

### Settings Caching

```php
class SettingsRepository
{
    public function get($key, $default = null)
    {
        return Cache::rememberForever("settings.{$key}", function() use ($key, $default) {
            return DB::table('settings')
                ->where('key', $key)
                ->value('value') ?? $default;
        });
    }
    
    public function set($key, $value)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
        
        Cache::forget("settings.{$key}");
    }
    
    public function all()
    {
        return Cache::rememberForever('settings.all', function() {
            return DB::table('settings')->pluck('value', 'key')->all();
        });
    }
}
```

### API Response Caching

```php
class ApiController
{
    public function stats()
    {
        $stats = Cache::remember('api.stats', 300, function() {
            return [
                'users' => User::count(),
                'posts' => Post::count(),
                'comments' => Comment::count(),
                'active_today' => User::whereDate('last_active_at', today())->count()
            ];
        });
        
        return response()->json($stats);
    }
}
```

### Expensive Computation Caching

```php
class ReportService
{
    public function generateSalesReport($startDate, $endDate)
    {
        $key = "report.sales.{$startDate}.{$endDate}";
        
        return Cache::remember($key, 3600, function() use ($startDate, $endDate) {
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->with('items.product')
                ->get();
            
            return [
                'total_revenue' => $orders->sum('total'),
                'total_orders' => $orders->count(),
                'average_order' => $orders->avg('total'),
                'top_products' => $this->getTopProducts($orders),
                'daily_breakdown' => $this->getDailyBreakdown($orders)
            ];
        });
    }
}
```

### Cache Lock for Critical Sections

```php
class ImportService
{
    public function importUsers($file)
    {
        $lock = Cache::lock('import:users', 300);
        
        if (!$lock->get()) {
            throw new Exception('Another import is already running');
        }
        
        try {
            $users = $this->parseFile($file);
            
            foreach ($users as $userData) {
                User::create($userData);
            }
            
            Cache::tags('users')->flush();
        } finally {
            $lock->release();
        }
    }
}
```

### Rate Limiting with Cache

```php
class RateLimiter
{
    public function attempt($key, $maxAttempts = 5, $decayMinutes = 1)
    {
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return false;
        }
        
        Cache::put($key, $attempts + 1, $decayMinutes * 60);
        
        return true;
    }
    
    public function tooManyAttempts($key, $maxAttempts = 5)
    {
        return Cache::get($key, 0) >= $maxAttempts;
    }
    
    public function clear($key)
    {
        Cache::forget($key);
    }
    
    public function retriesLeft($key, $maxAttempts = 5)
    {
        $attempts = Cache::get($key, 0);
        return $maxAttempts - $attempts;
    }
}

// Usage
$limiter = new RateLimiter();
$key = 'login:' . $request->ip();

if ($limiter->tooManyAttempts($key)) {
    return response()->json([
        'error' => 'Too many login attempts. Please try again later.'
    ], 429);
}

if ($this->authenticate($request)) {
    $limiter->clear($key);
    return $this->success();
}

$limiter->attempt($key);
```

### Cache Warming

```php
class CacheWarmer
{
    public function warm()
    {
        // Warm popular posts
        Cache::put('posts.popular', Post::popular()->take(10)->get(), 3600);
        
        // Warm categories
        Cache::put('categories.all', Category::with('children')->get(), 7200);
        
        // Warm settings
        Cache::forever('settings.all', DB::table('settings')->pluck('value', 'key'));
        
        // Warm user counts
        Cache::put('stats.users', User::count(), 1800);
        Cache::put('stats.posts', Post::count(), 1800);
    }
}
```

### Selective Cache Invalidation

```php
class Post extends Model
{
    protected static function booted()
    {
        static::created(function($post) {
            Cache::tags(['posts'])->flush();
            Cache::forget('posts.count');
        });
        
        static::updated(function($post) {
            Cache::forget("post:{$post->id}");
            Cache::tags(['posts', "category:{$post->category_id}"])->flush();
        });
        
        static::deleted(function($post) {
            Cache::forget("post:{$post->id}");
            Cache::tags(['posts'])->flush();
        });
    }
}
```

---

## Best Practices

### Cache Key Naming

```php
// Good - descriptive and hierarchical
Cache::put('user:123:profile', $profile, 3600);
Cache::put('posts:category:5', $posts, 600);
Cache::put('stats:daily:2024-01-01', $stats, 86400);

// Bad - unclear or flat
Cache::put('u123', $profile, 3600);
Cache::put('data', $posts, 600);
```

### TTL Selection

```php
// Frequently changing data - short TTL
Cache::put('trending:posts', $posts, 300); // 5 minutes

// Moderately stable data - medium TTL
Cache::put('user:profile', $profile, 3600); // 1 hour

// Rarely changing data - long TTL
Cache::put('categories', $categories, 86400); // 24 hours

// Static configuration - forever
Cache::forever('app:config', $config);
```

### Tag Organization

```php
// Use tags for related cache entries
Cache::tags(['users', 'profiles'])->put("user:{$id}", $user, 3600);
Cache::tags(['posts', "author:{$authorId}"])->put("post:{$id}", $post, 600);

// Flush related caches together
Cache::tags(['users'])->flush(); // Clear all user-related cache
Cache::tags(['posts', 'comments'])->flush(); // Clear posts and comments
```

---

## Next Steps

- [Queue Classes](queue.md)
- [Events Classes](events.md)
- [Mail Classes](mail.md)
- [Storage Classes](storage.md)
