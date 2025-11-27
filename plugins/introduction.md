# Plugin System

Extend NeoPhp functionality with plugins.

## What is a Plugin?

A plugin is a self-contained module that adds functionality to your NeoPhp application. Plugins can:

- Add new routes and controllers
- Register service providers
- Add CLI commands
- Hook into application lifecycle
- Provide reusable components
- Extend core functionality

## Plugin Structure

```
plugins/
└── my-plugin/
    ├── plugin.json           # Plugin metadata
    ├── Plugin.php            # Main plugin class
    ├── routes/
    │   ├── web.php          # Web routes
    │   └── api.php          # API routes
    ├── src/
    │   ├── Controllers/     # Plugin controllers
    │   ├── Models/          # Plugin models
    │   ├── Services/        # Plugin services
    │   └── Commands/        # CLI commands
    ├── views/               # Plugin views
    ├── config/              # Plugin configuration
    ├── migrations/          # Database migrations
    ├── assets/              # CSS, JS, images
    └── README.md            # Plugin documentation
```

## Creating a Plugin

### Manual Creation

1. Create plugin directory:

```bash
mkdir -p plugins/my-plugin
```

2. Create `plugin.json`:

```json
{
    "name": "my-plugin",
    "title": "My Awesome Plugin",
    "description": "Description of what this plugin does",
    "version": "1.0.0",
    "author": "Your Name",
    "author_url": "https://example.com",
    "namespace": "MyPlugin",
    "requires": {
        "neophp": "^1.0",
        "php": "^8.1"
    },
    "autoload": {
        "psr-4": {
            "MyPlugin\\": "src/"
        }
    }
}
```

3. Create `Plugin.php`:

```php
<?php

namespace MyPlugin;

use NeoPhp\Foundation\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin
{
    /**
     * Plugin activation
     */
    public function activate(): void
    {
        // Run migrations
        $this->runMigrations();
        
        // Create default settings
        $this->createDefaults();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate(): void
    {
        // Clean up
    }
    
    /**
     * Plugin boot
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutes();
        
        // Load views
        $this->loadViews();
        
        // Register commands
        $this->registerCommands();
        
        // Register hooks
        $this->registerHooks();
    }
    
    /**
     * Load plugin routes
     */
    protected function loadRoutes(): void
    {
        // Web routes
        if (file_exists($this->path('routes/web.php'))) {
            require $this->path('routes/web.php');
        }
        
        // API routes
        if (file_exists($this->path('routes/api.php'))) {
            require $this->path('routes/api.php');
        }
    }
    
    /**
     * Register CLI commands
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \MyPlugin\Commands\MyCommand::class,
        ]);
    }
    
    /**
     * Register hooks
     */
    protected function registerHooks(): void
    {
        $this->addAction('user.created', [$this, 'onUserCreated']);
        $this->addFilter('product.price', [$this, 'filterPrice']);
    }
    
    /**
     * Handle user created event
     */
    public function onUserCreated($user): void
    {
        // Send welcome email
    }
    
    /**
     * Filter product price
     */
    public function filterPrice(float $price): float
    {
        // Apply discount
        return $price * 0.9;
    }
    
    /**
     * Run migrations
     */
    protected function runMigrations(): void
    {
        $migrator = $this->app->make('migrator');
        $migrator->run($this->path('migrations'));
    }
    
    /**
     * Create default settings
     */
    protected function createDefaults(): void
    {
        // Create default data
    }
}
```

### Using CLI

```bash
php neo make:plugin MyPlugin --description="My awesome plugin"
```

This generates the complete plugin structure.

## Plugin Lifecycle

### Activation

Runs once when plugin is first activated:

```php
public function activate(): void
{
    // Run migrations
    $this->runMigrations();
    
    // Create default data
    Setting::create([
        'key' => 'myplugin.enabled',
        'value' => true
    ]);
    
    // Schedule tasks
    $this->schedule();
}
```

### Boot

Runs every request after plugin is activated:

```php
public function boot(): void
{
    // Load routes
    $this->loadRoutes();
    
    // Load views
    $this->loadViews();
    
    // Register services
    $this->app->singleton(MyService::class);
    
    // Add hooks
    $this->registerHooks();
}
```

### Deactivation

Runs when plugin is deactivated:

```php
public function deactivate(): void
{
    // Clean up temporary data
    Cache::forget('myplugin.*');
    
    // Remove scheduled tasks
    $this->unschedule();
}
```

### Uninstall

Runs when plugin is completely removed:

```php
public function uninstall(): void
{
    // Drop tables
    Schema::dropIfExists('myplugin_data');
    
    // Remove settings
    Setting::where('key', 'like', 'myplugin.%')->delete();
    
    // Remove files
    Storage::deleteDirectory('plugins/myplugin');
}
```

## Complete Example: E-commerce Plugin

### Directory Structure

```
plugins/ecommerce/
├── plugin.json
├── Plugin.php
├── routes/
│   ├── web.php
│   └── api.php
├── src/
│   ├── Controllers/
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   └── CheckoutController.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Cart.php
│   │   └── Order.php
│   ├── Services/
│   │   ├── CartService.php
│   │   ├── PaymentService.php
│   │   └── ShippingService.php
│   └── Commands/
│       └── SyncProductsCommand.php
├── views/
│   ├── products/
│   │   ├── index.php
│   │   └── show.php
│   ├── cart/
│   │   └── index.php
│   └── checkout/
│       └── index.php
├── migrations/
│   ├── 001_create_products_table.php
│   ├── 002_create_carts_table.php
│   └── 003_create_orders_table.php
├── config/
│   └── ecommerce.php
└── assets/
    ├── css/
    │   └── ecommerce.css
    └── js/
        └── cart.js
```

### plugin.json

```json
{
    "name": "ecommerce",
    "title": "E-commerce Plugin",
    "description": "Complete e-commerce solution with products, cart, and checkout",
    "version": "1.0.0",
    "author": "NeoPhp Team",
    "author_url": "https://neophp.dev",
    "namespace": "Ecommerce",
    "requires": {
        "neophp": "^1.0",
        "php": "^8.1"
    },
    "autoload": {
        "psr-4": {
            "Ecommerce\\": "src/"
        }
    },
    "config": {
        "currency": "USD",
        "tax_rate": 0.1,
        "shipping_methods": ["standard", "express"],
        "payment_gateways": ["stripe", "paypal"]
    }
}
```

### Plugin.php

```php
<?php

namespace Ecommerce;

use NeoPhp\Foundation\Plugin\AbstractPlugin;
use Ecommerce\Services\{CartService, PaymentService, ShippingService};

class Plugin extends AbstractPlugin
{
    /**
     * Plugin activation
     */
    public function activate(): void
    {
        // Run migrations
        $this->runMigrations();
        
        // Create default categories
        $this->createDefaultCategories();
        
        // Create default settings
        $this->createSettings();
    }
    
    /**
     * Plugin boot
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutes();
        
        // Load views
        $this->loadViews();
        
        // Register services
        $this->registerServices();
        
        // Register commands
        $this->registerCommands();
        
        // Register hooks
        $this->registerHooks();
        
        // Load assets
        $this->loadAssets();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate(): void
    {
        // Clear cart cache
        Cache::tags(['ecommerce.cart'])->flush();
    }
    
    /**
     * Plugin uninstall
     */
    public function uninstall(): void
    {
        // Drop tables
        $this->dropTables();
        
        // Remove settings
        Setting::where('key', 'like', 'ecommerce.%')->delete();
    }
    
    /**
     * Load routes
     */
    protected function loadRoutes(): void
    {
        // Web routes
        require $this->path('routes/web.php');
        
        // API routes
        require $this->path('routes/api.php');
    }
    
    /**
     * Register services
     */
    protected function registerServices(): void
    {
        $this->app->singleton(CartService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(ShippingService::class);
    }
    
    /**
     * Register commands
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Ecommerce\Commands\SyncProductsCommand::class,
        ]);
    }
    
    /**
     * Register hooks
     */
    protected function registerHooks(): void
    {
        // Add to cart event
        $this->addAction('cart.item_added', [$this, 'onItemAdded']);
        
        // Order placed event
        $this->addAction('order.placed', [$this, 'onOrderPlaced']);
        
        // Filter product price
        $this->addFilter('product.price', [$this, 'applyDiscount'], 10, 2);
    }
    
    /**
     * Handle item added to cart
     */
    public function onItemAdded($item): void
    {
        // Update stock
        $product = Product::find($item['product_id']);
        $product->decrement('stock_quantity', $item['quantity']);
    }
    
    /**
     * Handle order placed
     */
    public function onOrderPlaced($order): void
    {
        // Send confirmation email
        Mail::to($order->user)->send(new OrderConfirmation($order));
        
        // Notify admin
        Mail::to(config('admin.email'))->send(new NewOrder($order));
    }
    
    /**
     * Apply discount to product price
     */
    public function applyDiscount(float $price, $product): float
    {
        if ($product->is_on_sale) {
            return $price * (1 - $product->discount_percentage / 100);
        }
        return $price;
    }
    
    /**
     * Create default categories
     */
    protected function createDefaultCategories(): void
    {
        $categories = ['Electronics', 'Clothing', 'Books', 'Home & Garden'];
        
        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
    
    /**
     * Create default settings
     */
    protected function createSettings(): void
    {
        $settings = [
            'ecommerce.currency' => 'USD',
            'ecommerce.tax_rate' => 0.1,
            'ecommerce.free_shipping_threshold' => 50.00,
        ];
        
        foreach ($settings as $key => $value) {
            Setting::create(compact('key', 'value'));
        }
    }
    
    /**
     * Drop plugin tables
     */
    protected function dropTables(): void
    {
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_carts');
        Schema::dropIfExists('ecommerce_products');
    }
}
```

### routes/web.php

```php
<?php

use Ecommerce\Controllers\{ProductController, CartController, CheckoutController};

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Cart
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'add']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::delete('/cart/{id}', [CartController::class, 'remove']);

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index']);
Route::post('/checkout', [CheckoutController::class, 'process']);
Route::get('/checkout/success', [CheckoutController::class, 'success']);
```

### Services/CartService.php

```php
<?php

namespace Ecommerce\Services;

use Ecommerce\Models\{Cart, Product};

class CartService
{
    /**
     * Add item to cart
     */
    public function addItem(int $productId, int $quantity = 1): Cart
    {
        $product = Product::findOrFail($productId);
        
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $productId
        ]);
        
        $cart->quantity += $quantity;
        $cart->price = $product->price;
        $cart->save();
        
        // Fire hook
        do_action('cart.item_added', $cart);
        
        return $cart;
    }
    
    /**
     * Get cart items
     */
    public function getItems(): array
    {
        return Cart::where('user_id', auth()->id())
            ->with('product')
            ->get()
            ->toArray();
    }
    
    /**
     * Calculate total
     */
    public function getTotal(): float
    {
        $items = $this->getItems();
        
        $subtotal = array_reduce($items, function($carry, $item) {
            return $carry + ($item['quantity'] * $item['price']);
        }, 0);
        
        // Apply filters
        $total = apply_filters('cart.total', $subtotal);
        
        return $total;
    }
    
    /**
     * Clear cart
     */
    public function clear(): void
    {
        Cart::where('user_id', auth()->id())->delete();
        
        do_action('cart.cleared');
    }
}
```

## Plugin Configuration

### config/ecommerce.php

```php
<?php

return [
    'currency' => env('ECOMMERCE_CURRENCY', 'USD'),
    
    'tax_rate' => env('ECOMMERCE_TAX_RATE', 0.1),
    
    'shipping' => [
        'methods' => [
            'standard' => [
                'name' => 'Standard Shipping',
                'cost' => 5.00,
                'days' => '5-7'
            ],
            'express' => [
                'name' => 'Express Shipping',
                'cost' => 15.00,
                'days' => '2-3'
            ]
        ],
        'free_threshold' => 50.00
    ],
    
    'payment_gateways' => [
        'stripe' => [
            'enabled' => true,
            'api_key' => env('STRIPE_API_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY')
        ],
        'paypal' => [
            'enabled' => true,
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_SECRET')
        ]
    ]
];
```

## Managing Plugins

### List Plugins

```bash
php neo plugin:list
```

### Activate Plugin

```bash
php neo plugin:activate ecommerce
```

### Deactivate Plugin

```bash
php neo plugin:deactivate ecommerce
```

### Install Plugin

```bash
php neo plugin:install ecommerce.zip
```

### Uninstall Plugin

```bash
php neo plugin:uninstall ecommerce
```

## Best Practices

### 1. Use Namespace Prefix

```php
// Good ✅
namespace MyPlugin;

// Bad ❌
namespace App;  // Conflicts with main app
```

### 2. Clean Up on Deactivation

```php
// Good ✅
public function deactivate(): void
{
    Cache::forget('myplugin.*');
    Scheduler::unschedule('myplugin.*');
}
```

### 3. Use Hooks Instead of Modifying Core

```php
// Good ✅
$this->addFilter('product.price', [$this, 'applyDiscount']);

// Bad ❌
// Modifying core Product model
```

### 4. Provide Configuration

```php
// Good ✅
$config = $this->config('shipping.methods');

// Allow user customization
```

### 5. Document Your Plugin

Provide comprehensive README with:
- Installation instructions
- Configuration options
- Usage examples
- Hooks available
- API documentation

## Next Steps

- [Service Providers](service-providers.md)
- [Hooks System](../core/hooks.md)
- [CLI Commands](../cli/custom-commands.md)
- [Migrations](../database/migrations.md)
