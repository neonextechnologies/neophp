# Plugins

Plugins are self-contained packages that extend NeoPhp's functionality without modifying the core code. Think of them like WordPress plugins or browser extensions.

## What are Plugins?

Plugins allow you to:
- Add new features without touching core code
- Create reusable, distributable components
- Enable/disable functionality on demand
- Build modular applications
- Isolate third-party code

## Plugin Structure

```
plugins/blog/
├── BlogPlugin.php          # Main plugin class
├── Controllers/
│   ├── PostController.php
│   └── CommentController.php
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
├── assets/
│   ├── css/
│   │   └── blog.css
│   └── js/
│       └── blog.js
└── README.md
```

## Creating a Plugin

Generate a plugin using CLI:

```bash
php neo make:plugin Blog
```

This creates the basic structure:

```php
<?php

namespace Plugins\Blog;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Plugin\HookManager;

class BlogPlugin extends Plugin
{
    protected string $name = 'blog';
    protected string $version = '1.0.0';
    protected string $description = 'A blog plugin';
    protected string $author = 'Your Name';
    protected array $dependencies = [];

    public function install(): void
    {
        // Create database tables
        // Copy assets
        // Set default config
    }

    public function uninstall(): void
    {
        // Drop tables
        // Remove files
        // Clean up
    }

    public function boot(): void
    {
        // Register routes
        // Register hooks
        // Register services
    }
}
```

## Plugin Lifecycle

### 1. Installation

Called once when plugin is first installed:

```php
public function install(): void
{
    // Create database tables
    Schema::create('blog_posts', function(Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->timestamps();
    });
    
    Schema::create('blog_comments', function(Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained('blog_posts');
        $table->text('comment');
        $table->timestamps();
    });
    
    // Copy default config
    copy(
        __DIR__ . '/config/blog.php',
        config_path('blog.php')
    );
    
    // Create default data
    Post::create([
        'title' => 'Welcome to Blog',
        'content' => 'This is your first post!'
    ]);
}
```

### 2. Boot

Called every time the application starts (if plugin is active):

```php
public function boot(): void
{
    // Register routes
    $this->loadRoutes();
    
    // Register hooks
    $this->registerHooks();
    
    // Register services
    $this->registerServices();
    
    // Load views
    $this->loadViews();
}

protected function loadRoutes(): void
{
    Route::prefix('blog')->group(function() {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::post('/', [PostController::class, 'store']);
    });
}

protected function registerHooks(): void
{
    HookManager::addAction('user.created', function($user) {
        // Send welcome email with blog link
    });
    
    HookManager::addFilter('menu.items', function($items) {
        $items[] = ['title' => 'Blog', 'url' => '/blog'];
        return $items;
    });
}

protected function registerServices(): void
{
    $this->app->singleton('blog', function($app) {
        return new BlogService($app->make('db'));
    });
}
```

### 3. Uninstall

Called when plugin is removed:

```php
public function uninstall(): void
{
    // Drop tables
    Schema::dropIfExists('blog_comments');
    Schema::dropIfExists('blog_posts');
    
    // Remove config
    @unlink(config_path('blog.php'));
    
    // Remove assets
    File::deleteDirectory(public_path('plugins/blog'));
}
```

## Real-World Plugin Example

### E-commerce Plugin

```php
<?php

namespace Plugins\Shop;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Plugin\HookManager;
use NeoPhp\Database\Schema\Schema;
use NeoPhp\Database\Schema\Blueprint;

class ShopPlugin extends Plugin
{
    protected string $name = 'shop';
    protected string $version = '2.0.0';
    protected string $description = 'E-commerce functionality';
    protected string $author = 'Your Company';
    protected array $dependencies = ['payment', 'shipping'];

    public function install(): void
    {
        // Create products table
        Schema::create('products', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        // Create orders table
        Schema::create('orders', function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled']);
            $table->timestamps();
        });
        
        // Create order items table
        Schema::create('order_items', function(Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function boot(): void
    {
        // Register routes
        Route::prefix('shop')->group(function() {
            // Product routes
            Route::get('/products', [ProductController::class, 'index']);
            Route::get('/products/{id}', [ProductController::class, 'show']);
            
            // Cart routes
            Route::post('/cart/add', [CartController::class, 'add']);
            Route::get('/cart', [CartController::class, 'show']);
            
            // Checkout routes
            Route::post('/checkout', [CheckoutController::class, 'process']);
        });
        
        // Register hooks
        HookManager::addAction('order.created', [$this, 'onOrderCreated']);
        HookManager::addAction('order.paid', [$this, 'onOrderPaid']);
        
        HookManager::addFilter('product.price', [$this, 'applyDiscount']);
        
        // Register services
        $this->app->singleton('shop.cart', function($app) {
            return new CartService($app->make('db'), $app->make('cache'));
        });
        
        $this->app->singleton('shop.checkout', function($app) {
            return new CheckoutService(
                $app->make('db'),
                $app->make('payment'),
                $app->make('mail')
            );
        });
    }

    public function onOrderCreated(Order $order): void
    {
        // Send order confirmation email
        $mailer = $this->app->make('mail');
        $mailer->sendTemplate(
            $order->user->email,
            'Order Confirmation',
            'emails.order-confirmation',
            ['order' => $order]
        );
        
        // Log order
        logger()->info('Order created', ['order_id' => $order->id]);
    }

    public function onOrderPaid(Order $order): void
    {
        // Update stock
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            $product->stock -= $item->quantity;
            $product->save();
        }
        
        // Notify admin
        $mailer = $this->app->make('mail');
        $mailer->send(
            config('shop.admin_email'),
            'New Order Paid',
            "Order #{$order->id} has been paid"
        );
    }

    public function applyDiscount(float $price): float
    {
        // Apply 10% discount for Black Friday
        if ($this->isBlackFriday()) {
            return $price * 0.9;
        }
        return $price;
    }

    protected function isBlackFriday(): bool
    {
        $date = new \DateTime();
        return $date->format('m-d') === '11-24';
    }

    public function uninstall(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
}
```

## Plugin Dependencies

Declare dependencies on other plugins:

```php
protected array $dependencies = ['payment', 'shipping', 'email'];
```

NeoPhp ensures dependencies are loaded first.

## Plugin Configuration

Store plugin config in `config/` directory:

```php
// plugins/shop/config/shop.php
return [
    'currency' => 'USD',
    'tax_rate' => 0.07,
    'shipping_fee' => 5.00,
    'free_shipping_threshold' => 50.00,
    'admin_email' => 'admin@example.com',
];
```

Access config:

```php
$currency = config('shop.currency');
$taxRate = config('shop.tax_rate');
```

## Plugin Manager

Manage plugins programmatically:

```php
use NeoPhp\Plugin\PluginManager;

$pluginManager = new PluginManager($app);

// Discover plugins
$plugins = $pluginManager->discover();

// Install plugin
$pluginManager->install('shop');

// Activate plugin
$pluginManager->activate('shop');

// Deactivate plugin
$pluginManager->deactivate('shop');

// Uninstall plugin
$pluginManager->uninstall('shop');

// List all plugins
$all = $pluginManager->all();

// List active plugins
$active = $pluginManager->active();

// Check if plugin is active
if ($pluginManager->isActive('shop')) {
    // Shop plugin is active
}
```

## CLI Commands

Manage plugins via CLI:

```bash
# List all plugins
php neo plugin:list

# Install plugin
php neo plugin:install shop

# Activate plugin
php neo plugin:activate shop

# Deactivate plugin
php neo plugin:deactivate shop

# Uninstall plugin
php neo plugin:uninstall shop

# Create new plugin
php neo make:plugin MyPlugin
```

## Plugin Hooks

Plugins can interact using hooks:

### Actions

Do something when an event occurs:

```php
// Plugin A triggers action
HookManager::doAction('user.created', $user);

// Plugin B listens
HookManager::addAction('user.created', function($user) {
    // Send welcome email
});
```

### Filters

Modify data as it passes through:

```php
// Plugin A applies filter
$price = HookManager::applyFilters('product.price', $price, $product);

// Plugin B modifies the price
HookManager::addFilter('product.price', function($price, $product) {
    if ($product->on_sale) {
        return $price * 0.8; // 20% off
    }
    return $price;
});
```

## Plugin Assets

Serve plugin assets:

```php
public function boot(): void
{
    // Publish assets
    $this->publishes([
        __DIR__ . '/assets/css' => public_path('plugins/blog/css'),
        __DIR__ . '/assets/js' => public_path('plugins/blog/js'),
    ]);
}
```

Access in views:

```html
<link rel="stylesheet" href="/plugins/blog/css/blog.css">
<script src="/plugins/blog/js/blog.js"></script>
```

## Plugin Best Practices

### 1. Self-Contained

Keep all plugin code in the plugin directory:

```
plugins/blog/
├── BlogPlugin.php
├── Controllers/
├── Models/
├── views/
├── config/
└── assets/
```

### 2. Use Namespaces

```php
namespace Plugins\Blog;
namespace Plugins\Blog\Controllers;
namespace Plugins\Blog\Models;
```

### 3. Clean Uninstall

Remove everything on uninstall:

```php
public function uninstall(): void
{
    // Drop tables
    // Remove files
    // Clean cache
    // Remove config
}
```

### 4. Version Control

Use semantic versioning:

```php
protected string $version = '2.1.0';
```

### 5. Document Dependencies

```php
protected array $dependencies = ['payment', 'shipping'];
```

## Next Steps

- [Hook System](hooks.md)
- [Plugin Development Guide](../plugins/development.md)
- [Plugin Examples](../plugins/examples.md)
