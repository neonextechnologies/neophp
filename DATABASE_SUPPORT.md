# 🗄️ Multi-Database Support

NeoPhp รองรับหลายฐานข้อมูล!

## ✅ Databases รองรับ:

1. **MySQL** - ฐานข้อมูลยอดนิยม
2. **PostgreSQL** - Enterprise-grade RDBMS
3. **SQLite** - Embedded database
4. **SQL Server** - Microsoft SQL Server
5. **Turso** - Edge database (LibSQL)
6. **MongoDB** - NoSQL document database
7. **Redis** - In-memory key-value store

## 🚀 การใช้งาน

### 1. MySQL (Default)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neophp
DB_USERNAME=root
DB_PASSWORD=
```

```php
$users = User::all();
```

### 2. PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=neophp
DB_USERNAME=postgres
DB_PASSWORD=
```

### 3. SQLite

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 4. SQL Server

```env
DB_CONNECTION=sqlserver
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=neophp
DB_USERNAME=sa
DB_PASSWORD=
```

### 5. Turso (LibSQL)

```env
DB_CONNECTION=turso
TURSO_DATABASE_URL=https://your-db.turso.io
TURSO_AUTH_TOKEN=your-token
```

```php
$db = app('db');
$users = $db->query('SELECT * FROM users', []);
```

### 6. MongoDB

```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=neophp
DB_USERNAME=
DB_PASSWORD=
```

```php
$db = app('db')->getDriver();

// Insert
$id = $db->insertOne('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);

// Find
$user = $db->findOne('users', ['email' => 'john@example.com']);

// Find all
$users = $db->find('users', ['status' => 'active']);
```

### 7. Redis

```env
DB_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
REDIS_PREFIX=neophp:
```

```php
$redis = app('db')->getDriver();

// Set
$redis->set('key', 'value', 3600);

// Get
$value = $redis->get('key');

// Hash
$redis->hSet('user:1', 'name', 'John');
$name = $redis->hGet('user:1', 'name');
```

## 📦 Installation

```bash
# MySQL
composer require ext-pdo_mysql

# PostgreSQL
composer require ext-pdo_pgsql

# SQLite
composer require ext-pdo_sqlite

# MongoDB
composer require mongodb/mongodb

# Redis
composer require predis/predis
```

## 🔄 Switching Databases

เปลี่ยน database ได้ง่ายๆ แค่แก้ไข `.env`:

```env
# เปลี่ยนจาก MySQL → PostgreSQL
DB_CONNECTION=pgsql

# เปลี่ยนไป MongoDB
DB_CONNECTION=mongodb

# เปลี่ยนไป Turso
DB_CONNECTION=turso
```

Model และ code ไม่ต้องแก้!

## 🎯 Use Cases

**MySQL/PostgreSQL/SQL Server:** Traditional RDBMS  
**SQLite:** Development, Embedded apps  
**Turso:** Edge computing, Distributed apps  
**MongoDB:** Document storage, Flexible schema  
**Redis:** Cache, Session, Real-time data
