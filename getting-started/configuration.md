# Configuration

NeoPhp configuration is stored in the `.env` file and `config/` directory.

## Environment Configuration

The `.env` file contains environment-specific settings:

```env
# Application
APP_NAME=NeoPhp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neophp
DB_USERNAME=root
DB_PASSWORD=
DB_PREFIX=

# Cache
CACHE_DRIVER=file
CACHE_PREFIX=neophp_cache

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=sync

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=debug
```

## Configuration Files

Configuration files are stored in the `config/` directory:

### app.php

```php
<?php

return [
    'name' => env('APP_NAME', 'NeoPhp'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => 'en',
    'fallback_locale' => 'en',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    
    'providers' => [
        // Auto-discovered from app/Providers
    ],
];
```

### database.php

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'neophp'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', ''),
            'strict' => true,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'neophp'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => env('DB_PREFIX', ''),
            'schema' => 'public',
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', 'database/database.sqlite'),
            'prefix' => env('DB_PREFIX', ''),
        ],
    ],

    'migrations' => 'migrations',
];
```

### cache.php

```php
<?php

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => 'storage/cache',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'neophp_cache'),
];
```

## Accessing Configuration

Use the `config()` helper to access configuration values:

```php
// Get value
$appName = config('app.name');
$dbHost = config('database.connections.mysql.host');

// Set value at runtime
config(['app.name' => 'My App']);

// Get with default
$timezone = config('app.timezone', 'UTC');
```

## Environment Helper

Use the `env()` helper to access environment variables:

```php
$debug = env('APP_DEBUG', false);
$dbHost = env('DB_HOST', '127.0.0.1');
```

## Environment Types

### Local Development

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=true
LOG_LEVEL=info
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

## Caching Configuration

In production, cache your configuration for better performance:

```bash
php neo config:cache
```

Clear configuration cache:

```bash
php neo config:clear
```

## Security

### Protecting .env

Never commit `.env` to version control. Add it to `.gitignore`:

```
.env
.env.backup
```

### Generating Keys

Generate an application key:

```bash
php neo key:generate
```

This creates a secure random key for encryption.

## Next Steps

* [Directory Structure](directory-structure.md)
* [Service Providers](../core-concepts/service-providers.md)
