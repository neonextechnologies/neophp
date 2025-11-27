# Directory Structure

Understanding NeoPhp's directory structure will help you organize your application effectively.

## Root Directory

```
neophp/
├── app/                    # Application code
├── bootstrap/              # Bootstrap files
├── config/                 # Configuration files
├── database/               # Database files
├── plugins/                # Plugins directory
├── public/                 # Web root
├── src/                    # Framework source
├── storage/                # Storage files
├── vendor/                 # Composer dependencies
├── .env                    # Environment config
├── .env.example            # Example environment
├── .gitignore              # Git ignore rules
├── composer.json           # Composer dependencies
├── neo                     # CLI runner
└── README.md               # Documentation
```

## The App Directory

Contains your application code:

```
app/
├── Console/
│   └── Commands/           # Custom CLI commands
├── Controllers/            # HTTP controllers
├── Middleware/             # HTTP middleware
├── Models/                 # Data models
└── Providers/              # Service providers
```

### Console

Custom console commands:

```php
app/Console/Commands/
└── SendEmailCommand.php
```

### Controllers

HTTP request handlers:

```php
app/Controllers/
├── UserController.php
├── ProductController.php
└── OrderController.php
```

### Middleware

Request/response filters:

```php
app/Middleware/
├── AuthMiddleware.php
├── CorsMiddleware.php
└── RateLimitMiddleware.php
```

### Models

Data models with metadata:

```php
app/Models/
├── User.php
├── Product.php
└── Order.php
```

### Providers

Service providers:

```php
app/Providers/
├── AppServiceProvider.php
├── DatabaseServiceProvider.php
└── CacheServiceProvider.php
```

## The Bootstrap Directory

Bootstrap files for framework initialization:

```
bootstrap/
├── app.php                 # Application bootstrap
└── cache/                  # Cached files
```

## The Config Directory

Configuration files:

```
config/
├── app.php                 # Application config
├── database.php            # Database config
├── cache.php               # Cache config
├── queue.php               # Queue config
└── mail.php                # Mail config
```

## The Database Directory

Database-related files:

```
database/
├── migrations/             # Migration files
│   └── 2024_11_27_create_users_table.php
└── seeders/                # Seed files
    └── DatabaseSeeder.php
```

## The Plugins Directory

Plugin files:

```
plugins/
├── blog/
│   ├── BlogPlugin.php
│   ├── Controllers/
│   ├── Models/
│   └── views/
└── shop/
    ├── ShopPlugin.php
    └── ...
```

Each plugin is self-contained with its own:
* Controllers
* Models
* Views
* Routes
* Assets

## The Public Directory

Web-accessible files:

```
public/
├── index.php               # Entry point
├── css/                    # Stylesheets
├── js/                     # JavaScript
├── images/                 # Images
└── uploads/                # User uploads
```

## The Src Directory

Framework source code:

```
src/
├── Console/                # CLI framework
│   ├── Application.php
│   ├── Command.php
│   ├── Input.php
│   ├── Output.php
│   └── Commands/           # Built-in commands
├── Contracts/              # Interfaces
│   ├── DatabaseInterface.php
│   ├── CacheInterface.php
│   └── ...
├── Database/               # Database layer
│   ├── Migrations/
│   │   ├── Migration.php
│   │   └── Migrator.php
│   └── Schema/
│       ├── Schema.php
│       ├── Blueprint.php
│       └── ...
├── Foundation/             # Foundation layer
│   ├── ServiceProvider.php
│   └── ProviderManager.php
├── Forms/                  # Form builder
│   └── FormBuilder.php
├── Generator/              # Code generator
│   ├── Generator.php
│   └── stubs/
├── Metadata/               # Metadata system
│   ├── Table.php
│   ├── Field.php
│   ├── Relations.php
│   └── MetadataRepository.php
└── Plugin/                 # Plugin system
    ├── Plugin.php
    ├── PluginManager.php
    └── HookManager.php
```

## The Storage Directory

Application storage:

```
storage/
├── app/                    # Application storage
├── cache/                  # Cache files
├── logs/                   # Log files
└── uploads/                # Uploaded files
```

## The Vendor Directory

Composer dependencies (auto-generated, don't edit):

```
vendor/
├── composer/
├── psr/
└── ...
```

## Namespaces

NeoPhp uses PSR-4 autoloading:

```php
// Framework
namespace NeoPhp\Console;
namespace NeoPhp\Contracts;
namespace NeoPhp\Database;
namespace NeoPhp\Foundation;
namespace NeoPhp\Metadata;
namespace NeoPhp\Plugin;

// Application
namespace App\Controllers;
namespace App\Models;
namespace App\Providers;
namespace App\Middleware;

// Plugins
namespace Plugins\Blog;
namespace Plugins\Shop;
```

## Best Practices

### Organizing Controllers

Group related controllers:

```
app/Controllers/
├── Admin/
│   ├── DashboardController.php
│   └── UserController.php
├── Api/
│   └── ProductController.php
└── Web/
    └── HomeController.php
```

### Organizing Models

Group by domain:

```
app/Models/
├── User/
│   ├── User.php
│   ├── Role.php
│   └── Permission.php
└── Shop/
    ├── Product.php
    ├── Category.php
    └── Order.php
```

### Plugin Structure

Keep plugins self-contained:

```
plugins/blog/
├── BlogPlugin.php          # Main plugin class
├── Controllers/
│   └── PostController.php
├── Models/
│   ├── Post.php
│   └── Comment.php
├── views/
│   ├── index.blade.php
│   └── show.blade.php
├── routes/
│   └── web.php
├── config/
│   └── blog.php
└── assets/
    ├── css/
    └── js/
```

## Next Steps

* [Core Concepts](../core-concepts/foundation-architecture.md)
* [CLI Tools](../cli/introduction.md)
* [Service Providers](../core-concepts/service-providers.md)
