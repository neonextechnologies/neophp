# Hook System

NeoPhp's hook system provides a WordPress-style event-driven architecture, allowing plugins and components to interact without tight coupling.

## What are Hooks?

Hooks allow you to:
- Execute code at specific points in your application
- Modify data as it flows through the system
- Extend functionality without modifying core code
- Create plugin ecosystems
- Build event-driven applications

## Two Types of Hooks

### Actions
Do something when an event occurs (no return value):

```php
// Trigger an action
HookManager::doAction('user.created', $user);

// Listen to the action
HookManager::addAction('user.created', function($user) {
    // Send welcome email
    Mail::send($user->email, 'Welcome!', 'emails.welcome');
});
```

### Filters
Modify data as it passes through (must return value):

```php
// Apply a filter
$price = HookManager::applyFilters('product.price', 100);

// Modify the value
HookManager::addFilter('product.price', function($price) {
    return $price * 0.9; // 10% discount
});
```

## Using Actions

### Adding Action Hooks

```php
use NeoPhp\Plugin\HookManager;

// Simple callback
HookManager::addAction('user.created', function($user) {
    logger()->info("User created: {$user->name}");
});

// With priority (lower runs first, default: 10)
HookManager::addAction('user.created', function($user) {
    // This runs first (priority 5)
}, 5);

HookManager::addAction('user.created', function($user) {
    // This runs second (priority 10)
}, 10);

// With number of arguments
HookManager::addAction('order.placed', function($order, $user, $items) {
    // Process order
}, 10, 3); // Accepts 3 arguments
```

### Triggering Actions

```php
// Simple trigger
HookManager::doAction('app.booted');

// With single argument
HookManager::doAction('user.created', $user);

// With multiple arguments
HookManager::doAction('order.placed', $order, $user, $items);
```

### Common Action Points

```php
// Application lifecycle
HookManager::doAction('app.booting');
HookManager::doAction('app.booted');
HookManager::doAction('app.terminating');

// User events
HookManager::doAction('user.created', $user);
HookManager::doAction('user.updated', $user);
HookManager::doAction('user.deleted', $user);
HookManager::doAction('user.login', $user);
HookManager::doAction('user.logout', $user);

// Database events
HookManager::doAction('model.created', $model);
HookManager::doAction('model.updated', $model);
HookManager::doAction('model.deleted', $model);

// Request lifecycle
HookManager::doAction('request.received', $request);
HookManager::doAction('request.processed', $request, $response);
HookManager::doAction('response.sending', $response);
```

## Using Filters

### Adding Filter Hooks

```php
// Simple filter
HookManager::addFilter('user.name', function($name) {
    return strtoupper($name);
});

// With priority
HookManager::addFilter('product.price', function($price) {
    return $price * 0.9; // 10% discount
}, 5);

HookManager::addFilter('product.price', function($price) {
    return $price - 10; // $10 off
}, 10);

// With multiple arguments
HookManager::addFilter('product.price', function($price, $product, $user) {
    if ($user->isPremium()) {
        return $price * 0.8; // 20% for premium
    }
    return $price;
}, 10, 3);
```

### Applying Filters

```php
// Simple filter
$name = HookManager::applyFilters('user.name', $name);

// With additional arguments
$price = HookManager::applyFilters('product.price', $price, $product, $user);
```

### Common Filter Points

```php
// Content filters
$content = HookManager::applyFilters('content', $content);
$title = HookManager::applyFilters('title', $title);
$excerpt = HookManager::applyFilters('excerpt', $excerpt);

// Data filters
$data = HookManager::applyFilters('user.data', $data);
$price = HookManager::applyFilters('product.price', $price);
$tax = HookManager::applyFilters('order.tax', $tax, $order);

// Query filters
$query = HookManager::applyFilters('database.query', $query);
$results = HookManager::applyFilters('search.results', $results);

// Response filters
$headers = HookManager::applyFilters('response.headers', $headers);
$json = HookManager::applyFilters('response.json', $json);
```

## Real-World Examples

### Example 1: Email Notifications

```php
// In your User model
class User extends Model
{
    public function create(array $data): User
    {
        $user = parent::create($data);
        
        // Trigger action
        HookManager::doAction('user.created', $user);
        
        return $user;
    }
}

// In EmailPlugin
class EmailPlugin extends Plugin
{
    public function boot(): void
    {
        HookManager::addAction('user.created', [$this, 'sendWelcomeEmail']);
        HookManager::addAction('user.created', [$this, 'notifyAdmin'], 20);
    }
    
    public function sendWelcomeEmail(User $user): void
    {
        Mail::send($user->email, 'Welcome!', 'emails.welcome', [
            'user' => $user
        ]);
    }
    
    public function notifyAdmin(User $user): void
    {
        Mail::send(
            config('app.admin_email'),
            'New User Registration',
            'emails.admin.new-user',
            ['user' => $user]
        );
    }
}
```

### Example 2: Dynamic Pricing

```php
// In Product model
class Product extends Model
{
    public function getPrice(): float
    {
        $price = $this->price;
        
        // Apply filters
        return HookManager::applyFilters('product.price', $price, $this, auth()->user());
    }
}

// In DiscountPlugin
class DiscountPlugin extends Plugin
{
    public function boot(): void
    {
        HookManager::addFilter('product.price', [$this, 'applySeasonalDiscount'], 10, 3);
        HookManager::addFilter('product.price', [$this, 'applyMemberDiscount'], 20, 3);
    }
    
    public function applySeasonalDiscount(float $price, Product $product, ?User $user): float
    {
        // Black Friday discount
        if ($this->isBlackFriday()) {
            return $price * 0.7; // 30% off
        }
        
        // Summer sale
        if ($this->isSummerSale()) {
            return $price * 0.85; // 15% off
        }
        
        return $price;
    }
    
    public function applyMemberDiscount(float $price, Product $product, ?User $user): float
    {
        if (!$user) {
            return $price;
        }
        
        return match($user->membership_level) {
            'gold' => $price * 0.8,    // 20% off
            'silver' => $price * 0.9,   // 10% off
            'bronze' => $price * 0.95,  // 5% off
            default => $price
        };
    }
}
```

### Example 3: Content Moderation

```php
// In Post model
class Post extends Model
{
    public function create(array $data): Post
    {
        // Apply content filters before saving
        $data['title'] = HookManager::applyFilters('post.title', $data['title']);
        $data['content'] = HookManager::applyFilters('post.content', $data['content']);
        
        $post = parent::create($data);
        
        // Trigger action after creation
        HookManager::doAction('post.created', $post);
        
        return $post;
    }
}

// In ModerationPlugin
class ModerationPlugin extends Plugin
{
    public function boot(): void
    {
        HookManager::addFilter('post.title', [$this, 'filterBadWords']);
        HookManager::addFilter('post.content', [$this, 'filterBadWords']);
        HookManager::addFilter('post.content', [$this, 'autoLinkUrls'], 20);
        
        HookManager::addAction('post.created', [$this, 'checkSpam']);
    }
    
    public function filterBadWords(string $text): string
    {
        $badWords = ['badword1', 'badword2', 'badword3'];
        
        foreach ($badWords as $word) {
            $text = str_ireplace($word, str_repeat('*', strlen($word)), $text);
        }
        
        return $text;
    }
    
    public function autoLinkUrls(string $content): string
    {
        return preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank">$1</a>',
            $content
        );
    }
    
    public function checkSpam(Post $post): void
    {
        if ($this->isSpam($post->content)) {
            $post->status = 'pending';
            $post->save();
            
            // Notify moderator
            Mail::send(
                config('app.moderator_email'),
                'Possible Spam Detected',
                'emails.spam-detected',
                ['post' => $post]
            );
        }
    }
}
```

### Example 4: Analytics Tracking

```php
// In AnalyticsPlugin
class AnalyticsPlugin extends Plugin
{
    public function boot(): void
    {
        // Track user actions
        HookManager::addAction('user.login', [$this, 'trackLogin']);
        HookManager::addAction('user.logout', [$this, 'trackLogout']);
        
        // Track product views
        HookManager::addAction('product.viewed', [$this, 'trackProductView']);
        
        // Track purchases
        HookManager::addAction('order.completed', [$this, 'trackPurchase']);
    }
    
    public function trackLogin(User $user): void
    {
        Analytics::track('user.login', [
            'user_id' => $user->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()
        ]);
    }
    
    public function trackProductView(Product $product): void
    {
        Analytics::track('product.viewed', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
    }
    
    public function trackPurchase(Order $order): void
    {
        Analytics::track('order.completed', [
            'order_id' => $order->id,
            'total' => $order->total,
            'items_count' => $order->items->count(),
            'user_id' => $order->user_id,
            'timestamp' => now()
        ]);
    }
}
```

## Hook Priority

Hooks run in priority order (lower numbers first):

```php
// Priority 5 - runs first
HookManager::addAction('user.created', function($user) {
    logger()->debug('First');
}, 5);

// Priority 10 (default) - runs second
HookManager::addAction('user.created', function($user) {
    logger()->debug('Second');
});

// Priority 20 - runs last
HookManager::addAction('user.created', function($user) {
    logger()->debug('Third');
}, 20);
```

## Removing Hooks

```php
// Add a named callback
$callback = function($user) {
    logger()->info("User: {$user->name}");
};

HookManager::addAction('user.created', $callback);

// Remove it later
HookManager::removeAction('user.created', $callback);

// Or use a class method (easier to remove)
HookManager::addAction('user.created', [MyClass::class, 'method']);
HookManager::removeAction('user.created', [MyClass::class, 'method']);
```

## Checking if Hook Exists

```php
// Check if action has listeners
if (HookManager::hasAction('user.created')) {
    // Action exists
}

// Check if filter has listeners
if (HookManager::hasFilter('product.price')) {
    // Filter exists
}
```

## Best Practices

### 1. Use Descriptive Hook Names

```php
// Good ✅
HookManager::doAction('user.created', $user);
HookManager::doAction('order.payment.failed', $order, $error);

// Bad ❌
HookManager::doAction('event1', $data);
HookManager::doAction('hook', $stuff);
```

### 2. Document Your Hooks

```php
/**
 * Triggered when a user is created
 * 
 * @param User $user The created user
 */
HookManager::doAction('user.created', $user);
```

### 3. Use Priorities Wisely

```php
// Critical operations first
HookManager::addAction('user.created', [$this, 'createProfile'], 5);

// Normal operations
HookManager::addAction('user.created', [$this, 'sendEmail'], 10);

// Optional operations last
HookManager::addAction('user.created', [$this, 'trackAnalytics'], 20);
```

### 4. Always Return in Filters

```php
// Good ✅
HookManager::addFilter('price', function($price) {
    return $price * 1.1; // Add 10%
});

// Bad ❌
HookManager::addFilter('price', function($price) {
    $price * 1.1; // Forgot to return!
});
```

### 5. Keep Hooks Focused

```php
// Good ✅
HookManager::doAction('user.created', $user);
HookManager::doAction('user.verified', $user);

// Bad ❌
HookManager::doAction('user.all.events', $user, $eventType);
```

## Next Steps

- [Plugins](plugins.md)
- [Plugin Development](../plugins/development.md)
- [Plugin Hooks](../plugins/hooks.md)
