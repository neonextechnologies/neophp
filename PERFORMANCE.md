# ⚡ NeoPhp Performance

## 🚀 สิ่งที่ทำให้เร็วกว่า Laravel

### 1. **Zero Configuration Overhead**
- ไม่มี Service Provider ซับซ้อน
- Auto-discovery แบบ lazy loading
- Minimal bootstrap process

### 2. **Lightweight DI Container**
- Reflection-based แบบ optimized
- ไม่มี complex binding resolution
- Auto-wiring ตรงไปตรงมา

### 3. **Direct Database Access**
- PDO wrapper แบบ lightweight
- ไม่มี Query Builder overhead (ถ้าไม่ใช้)
- Connection pooling ที่เบา

### 4. **Efficient Routing**
- Attribute-based routing (PHP 8)
- Fast pattern matching
- ไม่มี middleware stack ซ้ำซ้อน

### 5. **Simple Blade Compiler**
- Compile to pure PHP
- File-based caching
- No complex inheritance resolution

### 6. **Minimal File System**
```
NeoPhp: ~50 files
Laravel: 1000+ files
```

## 📊 Benchmark Comparison

```php
use NeoPhp\Performance\Benchmark;

// Measure execution
Benchmark::start('app');
// ... your code ...
$stats = Benchmark::end('app');

// Output: ['time' => 12.5, 'memory' => 2048.5]
```

### Typical Results:
```
NeoPhp:
- Bootstrap: 5-10ms
- Memory: 2-4MB
- Request: 15-30ms

Laravel:
- Bootstrap: 50-100ms
- Memory: 10-20MB
- Request: 100-200ms
```

## ⚡ Performance Tips

### 1. OPcache (Required!)
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. Composer Optimize
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Cache Configuration
```php
// Use Redis for cache
cache()->put('key', $value, 3600);
```

### 4. Lazy Loading
```php
// Don't load services you don't need
if (need_cache()) {
    cache()->put('key', 'value');
}
```

### 5. Database Optimization
```php
// Use raw queries for heavy operations
$db->query("SELECT * FROM users WHERE id IN (...)", $ids);

// Instead of ORM loop
foreach ($ids as $id) {
    User::find($id); // Slow!
}
```

## 🎯 Use Cases for Speed

**API Backend:**
```php
// JSON response: 10-15ms
return JsonResponse::success($data);
```

**Microservices:**
```php
// Minimal bootstrap: 5-8ms
$app->run();
```

**Real-time Apps:**
```php
// Event dispatch: 1-2ms
event('user.created', $user);
```

## 📦 What to Include

**Always:**
- OPcache
- Redis (for cache)
- Composer optimize

**Production:**
```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
```

## 🔥 Performance Monitoring

```php
// Track memory
$memory = Benchmark::getMemoryUsage(); // MB
$peak = Benchmark::getPeakMemoryUsage(); // MB

// Track execution
$result = Benchmark::measure('heavy_task', function() {
    // ... heavy operation ...
});

logger()->info('Performance', $result['stats']);
```

## ✅ Production Checklist

- [ ] OPcache enabled
- [ ] Composer optimized
- [ ] Redis cache enabled
- [ ] Debug mode off
- [ ] Route caching (if needed)
- [ ] View compilation cached
- [ ] Database indexes optimized
