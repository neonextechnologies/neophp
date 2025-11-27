# Building an E-Commerce Platform

Create a complete e-commerce platform with product management, shopping cart, and payment processing.

## Project Overview

We'll build:
- Product catalog with categories
- Shopping cart system
- Order management
- Payment integration
- Inventory management
- Customer accounts

## Database Schema

### Create Migrations

```bash
php neo make:migration create_products_table
php neo make:migration create_categories_table
php neo make:migration create_orders_table
php neo make:migration create_order_items_table
php neo make:migration create_cart_items_table
php neo make:migration create_addresses_table
php neo make:migration create_payments_table
```

### Products Table

```php
<?php

use NeoPhp\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('products', function($table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('features')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('sku')->unique();
            $table->integer('stock')->default(0);
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable();
            $table->timestamps();
            
            $table->index(['category_id', 'is_active']);
            $table->fulltext(['name', 'description']);
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('products');
    }
};
```

### Orders Table

```php
public function up(): void
{
    $this->schema->create('orders', function($table) {
        $table->id();
        $table->string('order_number')->unique();
        $table->foreignId('user_id')->constrained();
        $table->foreignId('billing_address_id')->constrained('addresses');
        $table->foreignId('shipping_address_id')->constrained('addresses');
        $table->decimal('subtotal', 10, 2);
        $table->decimal('tax', 10, 2)->default(0);
        $table->decimal('shipping', 10, 2)->default(0);
        $table->decimal('discount', 10, 2)->default(0);
        $table->decimal('total', 10, 2);
        $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])
            ->default('pending');
        $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
            ->default('pending');
        $table->string('payment_method')->nullable();
        $table->text('notes')->nullable();
        $table->timestamp('shipped_at')->nullable();
        $table->timestamp('delivered_at')->nullable();
        $table->timestamps();
        
        $table->index(['user_id', 'status']);
        $table->index('order_number');
    });
}
```

### Order Items Table

```php
public function up(): void
{
    $this->schema->create('order_items', function($table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->onDelete('cascade');
        $table->foreignId('product_id')->constrained();
        $table->string('product_name');
        $table->string('product_sku');
        $table->integer('quantity');
        $table->decimal('price', 10, 2);
        $table->decimal('subtotal', 10, 2);
        $table->timestamps();
    });
}
```

### Cart Items Table

```php
public function up(): void
{
    $this->schema->create('cart_items', function($table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        $table->string('session_id')->nullable();
        $table->foreignId('product_id')->constrained();
        $table->integer('quantity')->default(1);
        $table->timestamps();
        
        $table->index(['user_id', 'product_id']);
        $table->index(['session_id', 'product_id']);
    });
}
```

### Addresses Table

```php
public function up(): void
{
    $this->schema->create('addresses', function($table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('full_name');
        $table->string('phone');
        $table->string('address_line_1');
        $table->string('address_line_2')->nullable();
        $table->string('city');
        $table->string('state');
        $table->string('postal_code');
        $table->string('country')->default('US');
        $table->boolean('is_default')->default(false);
        $table->timestamps();
    });
}
```

Run migrations:

```bash
php neo migrate
```

## Models

### Product Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Product extends Model
{
    protected array $fillable = [
        'category_id', 'name', 'slug', 'description', 'features',
        'price', 'sale_price', 'sku', 'stock', 'images',
        'is_active', 'is_featured', 'weight', 'dimensions'
    ];
    
    protected array $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'images' => 'array',
        'dimensions' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
    
    // Helpers
    public function getEffectivePrice(): float
    {
        return $this->sale_price ?? $this->price;
    }
    
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }
    
    public function getDiscountPercentage(): int
    {
        if (!$this->isOnSale()) {
            return 0;
        }
        
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }
    
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
    
    public function decreaseStock(int $quantity): void
    {
        $this->decrement('stock', $quantity);
    }
    
    public function increaseStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }
}
```

### Order Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Order extends Model
{
    protected array $fillable = [
        'order_number', 'user_id', 'billing_address_id', 'shipping_address_id',
        'subtotal', 'tax', 'shipping', 'discount', 'total',
        'status', 'payment_status', 'payment_method', 'notes',
        'shipped_at', 'delivered_at'
    ];
    
    protected array $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }
    
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    // Generate unique order number
    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }
    
    // Status checks
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
    
    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }
    
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }
    
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
```

### Cart Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class CartItem extends Model
{
    protected array $fillable = ['user_id', 'session_id', 'product_id', 'quantity'];
    
    protected array $casts = [
        'quantity' => 'integer'
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function getSubtotal(): float
    {
        return $this->product->getEffectivePrice() * $this->quantity;
    }
}
```

## Services

### CartService

```php
<?php

namespace App\Services;

use App\Models\{CartItem, Product};
use NeoPhp\Support\Facades\Auth;

class CartService
{
    public function getItems()
    {
        return CartItem::with('product')
            ->where($this->getIdentifier())
            ->get();
    }
    
    public function addItem(Product $product, int $quantity = 1): CartItem
    {
        $cartItem = CartItem::firstOrCreate(
            array_merge($this->getIdentifier(), ['product_id' => $product->id]),
            ['quantity' => 0]
        );
        
        $cartItem->increment('quantity', $quantity);
        
        return $cartItem->fresh();
    }
    
    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();
            return;
        }
        
        $item->update(['quantity' => $quantity]);
    }
    
    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }
    
    public function clear(): void
    {
        CartItem::where($this->getIdentifier())->delete();
    }
    
    public function getTotal(): float
    {
        return $this->getItems()->sum(function($item) {
            return $item->getSubtotal();
        });
    }
    
    public function getCount(): int
    {
        return $this->getItems()->sum('quantity');
    }
    
    private function getIdentifier(): array
    {
        return Auth::check()
            ? ['user_id' => Auth::id()]
            : ['session_id' => session()->getId()];
    }
    
    public function mergeGuestCart(): void
    {
        if (!Auth::check()) {
            return;
        }
        
        $guestItems = CartItem::where('session_id', session()->getId())->get();
        
        foreach ($guestItems as $item) {
            $this->addItem($item->product, $item->quantity);
            $item->delete();
        }
    }
}
```

### OrderService

```php
<?php

namespace App\Services;

use App\Models\{Order, OrderItem, Address};
use App\Events\OrderPlaced;

class OrderService
{
    public function __construct(
        private CartService $cart,
        private PaymentService $payment
    ) {}
    
    public function createOrder(array $data): Order
    {
        $cartItems = $this->cart->getItems();
        
        if ($cartItems->isEmpty()) {
            throw new \Exception('Cart is empty');
        }
        
        // Calculate totals
        $subtotal = $cartItems->sum(fn($item) => $item->getSubtotal());
        $tax = $subtotal * 0.1; // 10% tax
        $shipping = $this->calculateShipping($data['shipping_address_id']);
        $total = $subtotal + $tax + $shipping;
        
        // Create order
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => auth()->id(),
            'billing_address_id' => $data['billing_address_id'],
            'shipping_address_id' => $data['shipping_address_id'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
        ]);
        
        // Create order items
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name,
                'product_sku' => $cartItem->product->sku,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->getEffectivePrice(),
                'subtotal' => $cartItem->getSubtotal(),
            ]);
            
            // Decrease stock
            $cartItem->product->decreaseStock($cartItem->quantity);
        }
        
        // Clear cart
        $this->cart->clear();
        
        // Dispatch event
        event(new OrderPlaced($order));
        
        return $order;
    }
    
    public function processPayment(Order $order, array $paymentData): bool
    {
        try {
            $charge = $this->payment->charge(
                $order->total,
                $paymentData['token'],
                $order->user_id
            );
            
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing'
            ]);
            
            return true;
        } catch (\Exception $e) {
            $order->update(['payment_status' => 'failed']);
            return false;
        }
    }
    
    public function updateStatus(Order $order, string $status): void
    {
        $order->update(['status' => $status]);
        
        if ($status === 'shipped') {
            $order->update(['shipped_at' => now()]);
        }
        
        if ($status === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }
    }
    
    public function cancelOrder(Order $order): void
    {
        if (!$order->isPending() && !$order->isProcessing()) {
            throw new \Exception('Cannot cancel this order');
        }
        
        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increaseStock($item->quantity);
        }
        
        $order->update(['status' => 'cancelled']);
    }
    
    private function calculateShipping(int $addressId): float
    {
        // Simple flat rate shipping
        return 10.00;
    }
}
```

### PaymentService

```php
<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Charge;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    public function charge(float $amount, string $token, int $userId): Charge
    {
        return Charge::create([
            'amount' => $amount * 100, // Convert to cents
            'currency' => 'usd',
            'source' => $token,
            'metadata' => ['user_id' => $userId]
        ]);
    }
    
    public function refund(string $chargeId): void
    {
        Charge::retrieve($chargeId)->refund();
    }
}
```

## Controllers

### ProductController

```php
<?php

namespace App\Controllers;

use App\Models\{Product, Category};
use NeoPhp\Http\Request;

class ProductController
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->active()
            ->when($request->category, function($query, $category) {
                $query->whereHas('category', fn($q) => $q->where('slug', $category));
            })
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->sort === 'price_asc', fn($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_desc', fn($q) => $q->orderByDesc('price'))
            ->when($request->sort === 'newest', fn($q) => $q->latest())
            ->paginate(12);
        
        return view('products.index', [
            'products' => $products,
            'categories' => Category::withCount('products')->get()
        ]);
    }
    
    public function show(string $slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();
        
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->limit(4)
            ->get();
        
        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }
}
```

### CartController

```php
<?php

namespace App\Controllers;

use App\Models\Product;
use App\Services\CartService;
use NeoPhp\Http\Request;

class CartController
{
    public function __construct(private CartService $cart) {}
    
    public function index()
    {
        return view('cart.index', [
            'items' => $this->cart->getItems(),
            'total' => $this->cart->getTotal()
        ]);
    }
    
    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock
        ]);
        
        $this->cart->addItem($product, $validated['quantity']);
        
        return back()->with('success', 'Product added to cart');
    }
    
    public function update(Request $request, CartItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);
        
        $this->cart->updateQuantity($item, $validated['quantity']);
        
        return back()->with('success', 'Cart updated');
    }
    
    public function remove(CartItem $item)
    {
        $this->cart->removeItem($item);
        
        return back()->with('success', 'Item removed from cart');
    }
}
```

### CheckoutController

```php
<?php

namespace App\Controllers;

use App\Services\{CartService, OrderService};
use App\Models\Address;
use NeoPhp\Http\Request;

class CheckoutController
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders
    ) {}
    
    public function index()
    {
        if ($this->cart->getItems()->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }
        
        return view('checkout.index', [
            'items' => $this->cart->getItems(),
            'total' => $this->cart->getTotal(),
            'addresses' => auth()->user()->addresses
        ]);
    }
    
    public function process(Request $request)
    {
        $validated = $request->validate([
            'billing_address_id' => 'required|exists:addresses,id',
            'shipping_address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:card,paypal',
            'payment_token' => 'required',
            'notes' => 'nullable|string|max:500'
        ]);
        
        try {
            $order = $this->orders->createOrder($validated);
            
            $paymentSuccess = $this->orders->processPayment($order, [
                'token' => $validated['payment_token']
            ]);
            
            if (!$paymentSuccess) {
                return back()->with('error', 'Payment failed. Please try again.');
            }
            
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order placed successfully!');
                
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

### OrderController

```php
<?php

namespace App\Controllers;

use App\Models\Order;
use NeoPhp\Http\Request;

class OrderController
{
    public function index()
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
        
        return view('orders.index', ['orders' => $orders]);
    }
    
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        $order->load(['items.product', 'billingAddress', 'shippingAddress']);
        
        return view('orders.show', ['order' => $order]);
    }
}
```

## Routes

```php
<?php

use App\Controllers\{ProductController, CartController, CheckoutController, OrderController};

// Products
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout (authenticated only)
Route::middleware('auth')->group(function() {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});
```

## Testing

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Product, Order};
use App\Services\CartService;

class CheckoutTest extends TestCase
{
    public function test_can_complete_checkout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100, 'stock' => 10]);
        
        $cart = app(CartService::class);
        $cart->addItem($product, 2);
        
        $response = $this->actingAs($user)->post('/checkout', [
            'billing_address_id' => $user->addresses()->first()->id,
            'shipping_address_id' => $user->addresses()->first()->id,
            'payment_method' => 'card',
            'payment_token' => 'tok_visa'
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'subtotal' => 200
        ]);
        
        $this->assertEquals(8, $product->fresh()->stock);
    }
}
```

## Next Steps

- Add product reviews and ratings
- Implement wishlist functionality
- Add coupon/discount codes
- Create admin dashboard
- Add email notifications
- Implement inventory alerts

## Resources

- [Payment Integration](../advanced/payments.md)
- [Order Management](../features/orders.md)
- [Inventory System](../features/inventory.md)
