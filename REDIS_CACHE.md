# 🚀 Redis Cache Configuration

Redis ใช้สำหรับ **Cache เท่านั้น** ไม่เกี่ยวกับ Database!

## ⚙️ การตั้งค่า

### 1. ติดตั้ง Redis Extension

```bash
composer require predis/predis
```

### 2. Config `.env`

```env
# Cache Driver
CACHE_DRIVER=redis

# Redis Configuration (สำหรับ Cache)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_CACHE_DB=1
REDIS_PREFIX=neophp_cache:
```

### 3. Database Config (ไม่เกี่ยว Redis)

```env
# Database ใช้ MySQL/PostgreSQL/MongoDB ฯลฯ
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neophp
```

## 💡 ตัวอย่างการใช้งาน

### File Cache (Default)

```php
// .env
CACHE_DRIVER=file

// ใช้งาน
cache()->put('key', 'value', 3600);
$value = cache()->get('key');
```

### Redis Cache

```php
// .env
CACHE_DRIVER=redis

// ใช้งานเหมือนเดิม
cache()->put('user:1', $user, 3600);
$user = cache()->get('user:1');

// Remember pattern
$users = cache()->remember('users', 3600, function() {
    return User::all();
});

// Increment/Decrement
cache()->getDriver()->increment('views');
cache()->getDriver()->decrement('stock');
```

## 🔧 Advanced Redis Usage

```php
$redis = cache()->getDriver();

// Get Redis Client
$client = $redis->getRedis();

// Direct Redis commands
$client->set('key', 'value');
$client->lpush('list', 'item');
$client->sadd('set', 'member');
```

## 📊 Architecture

```
┌─────────────┐
│   Cache     │ ← cache() helper
└──────┬──────┘
       │
   ┌───▼────┬─────────┐
   │ File   │  Redis  │ ← Drivers
   └────────┴─────────┘
```

**Database** และ **Cache** ทำงานแยกกันอย่างสมบูรณ์!

- Database: MySQL, PostgreSQL, MongoDB, SQLite, etc.
- Cache: File หรือ Redis

## ✅ Benefits

- ✨ **Fast**: Redis in-memory cache
- 🔄 **Flexible**: สลับ driver ได้ง่าย
- 🎯 **Separate**: แยก DB และ Cache ชัดเจน
- 💪 **Powerful**: Redis features (increment, lists, sets, etc.)
