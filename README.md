# 🚀 NeoPhp Framework

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.0%20to%208.4-777BB4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Type](https://img.shields.io/badge/Type-Full%20Framework-blue?style=flat-square)

**A modern full-stack PHP framework for building web applications**  
*Built with contract-first architecture and metadata-driven development*

[Features](#-features) • [Installation](#-installation) • [Quick Start](#-quick-start) • [CLI Tools](#-cli-tools)

</div>

---

## 📖 About NeoPhp

**NeoPhp** is a modern full-stack PHP framework that provides everything you need to build robust web applications. It combines:

- 🏗️ **MVC Architecture** - Clean separation of concerns
- 🔌 **Plugin System** - Extensible with WordPress-style hooks
- 🎯 **Service Providers** - Deferred loading and dependency management
- 📝 **Metadata-Driven** - PHP 8 Attributes for declarative development
- 🛠️ **CLI Tools** - Code generation and migration system (`php neo`)
- 🗄️ **Database Layer** - Query builder, migrations, and seeders
- 🔐 **Security** - Built-in authentication and authorization
- ⚡ **Performance** - Optimized for speed with caching support

**Perfect for:**
- ✅ Building modern web applications
- ✅ Creating RESTful APIs
- ✅ Rapid application development
- ✅ Enterprise-level projects

---

## ⚡ Features

### 🏗️ Core Components

- **MVC Pattern** - Model-View-Controller architecture
- **Routing** - Fast and flexible routing system
- **Dependency Injection** - Powerful IoC container
- **Database** - Query builder, ORM, migrations
- **Views** - Blade templating engine
- **Validation** - Comprehensive validation system
- **Middleware** - HTTP middleware pipeline
- **Sessions** - Secure session management
- **Cache** - File, Redis, and in-memory cache
- **Queue** - Background job processing
- **Mail** - Email sending with multiple drivers
- **Events** - Event dispatcher system
- **Logging** - PSR-3 compatible logger

### 🎯 Service Provider System

Laravel-style providers with auto-discovery:

```php
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton('payment', fn() => new StripePayment(
            config('payment.stripe_key')
        ));
    }
    
    public function boot(): void {
        // Bootstrap services
    }
}
```

### 🔌 Plugin Architecture

WordPress-style hooks for extensibility:

```php
// Add action hook
HookManager::addAction('user.created', function($user) {
    Mail::send($user->email, 'Welcome!');
});

// Add filter hook
HookManager::addFilter('response.headers', function($headers) {
    $headers['X-Powered-By'] = 'NeoPhp';
    return $headers;
});
```

### 📝 Metadata-Driven Development

PHP 8 Attributes for models:

```php
#[Table('products')]
class Product extends Model
{
    #[Field(type: 'varchar', length: 255, required: true)]
    #[Validation(['required', 'min:3', 'max:100'])]
    public string $name;

    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validation(['required', 'numeric', 'min:0'])]
    public float $price;

    #[BelongsTo(target: Category::class)]
    public ?Category $category;
}
```

### 🛠️ CLI Tools

Powerful command-line tools for development:

```bash
# Code Generators
php neo make:controller UserController
php neo make:model Product -m
php neo make:migration create_orders_table
php neo make:middleware AuthMiddleware
php neo make:provider PaymentServiceProvider

# Database Migrations
php neo migrate                    # Run migrations
php neo migrate:rollback          # Rollback last batch
php neo migrate:status            # Show status
php neo migrate:refresh           # Reset + re-run

# Development
php neo serve                     # Development server
php neo cache:clear              # Clear cache
php neo db:seed                  # Run seeders
```

---

## 🚀 Installation

### Requirements

- PHP 8.0 or higher
- Composer
- MySQL/PostgreSQL/SQLite (optional)

### Install

```bash
# Clone repository
git clone https://github.com/neonextechnologies/neophp.git myapp
cd myapp

# Install dependencies
composer install

# Setup environment
cp .env.example .env

# Configure database in .env (optional)
nano .env

# Run migrations (if using database)
php neo migrate

# Start development server
php neo serve
```

Visit: http://localhost:8000

---

## 📖 Quick Start

### 1. Define Routes

```php
// routes/web.php
use App\Controllers\ProductController;

Route::get('/', function() {
    return view('home');
});

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
```

### 2. Create Controller

```php
// app/Controllers/ProductController.php
namespace App\Controllers;

use NeoPhp\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        return view('products.index', ['products' => $products]);
    }
    
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'name' => 'required|min:3',
            'price' => 'required|numeric|min:0'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }
        
        $product = Product::create($request->all());
        return redirect('/products')->with('success', 'Product created!');
    }
}
```

### 3. Create Model

```php
// app/Models/Product.php
namespace App\Models;

use NeoPhp\Database\Model;

#[Table('products')]
class Product extends Model
{
    #[Field(type: 'varchar', length: 255, required: true)]
    public string $name;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    public float $price;
    
    #[BelongsTo(target: Category::class)]
    public function category() {}
}
```

### 4. Create View

```blade
{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Products</h1>
    
    @if(count($products) > 0)
        <ul>
            @foreach($products as $product)
                <li>{{ $product->name }} - ${{ $product->price }}</li>
            @endforeach
        </ul>
    @else
        <p>No products found.</p>
    @endif
@endsection
```

---

## 🏗️ Project Structure

```
neophp/
├── app/
│   ├── Controllers/              # Your controllers
│   ├── Models/                   # Your models
│   ├── Middleware/               # Your middleware
│   ├── Providers/                # Your service providers
│   └── AppModule.php             # Application module
├── bootstrap/
│   └── app.php                   # Bootstrap file
├── config/                       # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── cache.php
│   └── ...
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── public/
│   ├── index.php                 # Entry point
│   └── .htaccess
├── resources/
│   └── views/                    # Blade templates
├── routes/
│   └── web.php                   # Route definitions
├── src/                          # Framework core
│   ├── Core/
│   ├── Http/
│   ├── Database/
│   ├── Foundation/
│   └── ...
├── storage/                      # Storage directory
│   ├── cache/
│   ├── logs/
│   └── ...
├── tests/                        # Test files
├── .env.example                  # Environment template
├── composer.json
├── neo                           # CLI tool
└── README.md
```

---

## 📚 Documentation

### Core Concepts

- **Routing** - Define your application routes
- **Controllers** - Handle HTTP requests
- **Models** - Define your data models
- **Views** - Blade templating engine
- **Validation** - Validate user input
- **Middleware** - Filter HTTP requests
- **Service Providers** - Bootstrap services
- **Dependency Injection** - IoC container

### Advanced Topics

- **Plugin System** - Extend functionality with hooks
- **Metadata** - Declarative development with attributes
- **CLI Tools** - Code generation and commands
- **Migrations** - Database schema management
- **Events** - Event-driven programming
- **Queue** - Background job processing

---

## 🤝 Contributing

Contributions are welcome! Please read our contributing guidelines.

### Development Setup

```bash
git clone https://github.com/neonextechnologies/neophp.git
cd neophp
composer install
composer dump-autoload
```

---

## 📄 License

MIT License - see LICENSE file for details.

---

## 🙏 Acknowledgments

- **Laravel** - Inspiration for service providers and CLI
- **Symfony** - Component design patterns
- **WordPress** - Plugin hook system

---

<div align="center">

**Built with ❤️ by [Neonex Technologies](https://neonex.co.th)**

[![GitHub Stars](https://img.shields.io/github/stars/neonextechnologies/neophp?style=social)](https://github.com/neonextechnologies/neophp)

</div>
