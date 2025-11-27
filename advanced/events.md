# Event System

Build decoupled applications using event-driven architecture.

## What are Events?

Events provide a simple observer pattern implementation, allowing you to subscribe and listen for events in your application.

## Configuration

Configure events in `config/events.php`:

```php
return [
    'listeners' => [
        'App\Events\UserRegistered' => [
            'App\Listeners\SendWelcomeEmail',
            'App\Listeners\CreateUserProfile',
            'App\Listeners\NotifyAdmins',
        ],
        
        'App\Events\OrderPlaced' => [
            'App\Listeners\SendOrderConfirmation',
            'App\Listeners\UpdateInventory',
            'App\Listeners\ProcessPayment',
        ],
    ],
];
```

## Creating Events

### Basic Event

```php
<?php

namespace App\Events;

class UserRegistered
{
    public function __construct(
        public User $user,
        public string $ipAddress
    ) {}
}
```

### Event with Serialization

```php
<?php

namespace App\Events;

use NeoPhp\Events\SerializesModels;

class OrderPlaced
{
    use SerializesModels;
    
    public function __construct(
        public Order $order,
        public array $metadata = []
    ) {}
}
```

## Creating Listeners

### Basic Listener

```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\WelcomeEmail;
use NeoPhp\Mail\Facades\Mail;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user->email)
            ->send(new WelcomeEmail($event->user));
    }
}
```

### Listener with Dependencies

```php
<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\{InventoryService, NotificationService};

class UpdateInventory
{
    public function __construct(
        private InventoryService $inventory,
        private NotificationService $notifications
    ) {}
    
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;
        
        foreach ($order->items as $item) {
            $this->inventory->decreaseStock($item->product_id, $item->quantity);
        }
        
        // Check low stock
        $lowStockProducts = $this->inventory->getLowStockProducts();
        
        if ($lowStockProducts->isNotEmpty()) {
            $this->notifications->notifyLowStock($lowStockProducts);
        }
    }
}
```

## Dispatching Events

### Basic Dispatch

```php
use App\Events\UserRegistered;

$user = User::create($data);

// Dispatch event
event(new UserRegistered($user, request()->ip()));
```

### Using Event Facade

```php
use NeoPhp\Events\Facades\Event;

Event::dispatch(new UserRegistered($user, request()->ip()));
```

## Registering Listeners

### Event Service Provider

```php
<?php

namespace App\Providers;

use NeoPhp\Events\EventServiceProvider as ServiceProvider;
use App\Events\{UserRegistered, OrderPlaced};
use App\Listeners\{SendWelcomeEmail, UpdateInventory};

class EventServiceProvider extends ServiceProvider
{
    protected array $listen = [
        UserRegistered::class => [
            SendWelcomeEmail::class,
            CreateUserProfile::class,
        ],
        
        OrderPlaced::class => [
            SendOrderConfirmation::class,
            UpdateInventory::class,
        ],
    ];
    
    public function boot(): void
    {
        parent::boot();
    }
}
```

### Manual Registration

```php
use NeoPhp\Events\Facades\Event;

Event::listen(UserRegistered::class, function($event) {
    logger()->info('User registered', [
        'user_id' => $event->user->id,
        'ip' => $event->ipAddress
    ]);
});
```

## Complete Examples

### User Registration Flow

**Event:**
```php
<?php

namespace App\Events;

use App\Models\User;

class UserRegistered
{
    public function __construct(
        public User $user,
        public string $source = 'web'
    ) {}
}
```

**Listeners:**
```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\WelcomeEmail;
use NeoPhp\Mail\Facades\Mail;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user->email)
            ->send(new WelcomeEmail($event->user));
    }
}

class CreateUserProfile
{
    public function handle(UserRegistered $event): void
    {
        $event->user->profile()->create([
            'bio' => '',
            'avatar' => 'default.png',
            'preferences' => []
        ]);
    }
}

class NotifyAdmins
{
    public function handle(UserRegistered $event): void
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new NewUserNotification($event->user)
            );
        }
    }
}
```

**Usage:**
```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password)
]);

event(new UserRegistered($user, 'registration_form'));
```

### Order Processing Flow

**Event:**
```php
<?php

namespace App\Events;

use App\Models\Order;

class OrderPlaced
{
    public function __construct(
        public Order $order,
        public string $paymentMethod
    ) {}
}
```

**Listeners:**
```php
<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\{PaymentService, InventoryService, InvoiceService};

class ProcessPayment
{
    public function __construct(private PaymentService $payment) {}
    
    public function handle(OrderPlaced $event): void
    {
        $charge = $this->payment->charge(
            $event->order->total,
            $event->paymentMethod,
            $event->order->user_id
        );
        
        $event->order->update([
            'payment_status' => 'completed',
            'transaction_id' => $charge->id
        ]);
    }
}

class UpdateInventory
{
    public function __construct(private InventoryService $inventory) {}
    
    public function handle(OrderPlaced $event): void
    {
        foreach ($event->order->items as $item) {
            $this->inventory->reserve($item->product_id, $item->quantity);
        }
    }
}

class GenerateInvoice
{
    public function __construct(private InvoiceService $invoice) {}
    
    public function handle(OrderPlaced $event): void
    {
        $pdf = $this->invoice->generate($event->order);
        
        Storage::put("invoices/{$event->order->id}.pdf", $pdf);
        
        $event->order->update([
            'invoice_generated_at' => now()
        ]);
    }
}
```

**Usage:**
```php
$order = Order::create([
    'user_id' => auth()->id(),
    'total' => $cart->total(),
    'status' => 'pending'
]);

event(new OrderPlaced($order, $request->payment_method));
```

### File Upload Processing

**Event:**
```php
<?php

namespace App\Events;

use App\Models\Upload;

class FileUploaded
{
    public function __construct(
        public Upload $upload,
        public string $originalPath
    ) {}
}
```

**Listeners:**
```php
<?php

namespace App\Listeners;

use App\Events\FileUploaded;
use App\Jobs\{ProcessImageJob, ScanVirusJob, ExtractMetadataJob};

class QueueImageProcessing
{
    public function handle(FileUploaded $event): void
    {
        if ($event->upload->isImage()) {
            ProcessImageJob::dispatch($event->upload->id);
        }
    }
}

class ScanForViruses
{
    public function handle(FileUploaded $event): void
    {
        ScanVirusJob::dispatch($event->upload->id);
    }
}

class ExtractMetadata
{
    public function handle(FileUploaded $event): void
    {
        ExtractMetadataJob::dispatch($event->upload->id);
    }
}

class NotifyUploadComplete
{
    public function handle(FileUploaded $event): void
    {
        $user = $event->upload->user;
        
        Mail::to($user->email)->send(
            new UploadCompleteNotification($event->upload)
        );
    }
}
```

### Model Events

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use App\Events\{UserCreated, UserUpdated, UserDeleted};

class User extends Model
{
    protected static function boot(): void
    {
        parent::boot();
        
        static::created(function($user) {
            event(new UserCreated($user));
        });
        
        static::updated(function($user) {
            event(new UserUpdated($user));
        });
        
        static::deleted(function($user) {
            event(new UserDeleted($user));
        });
    }
}
```

## Queued Listeners

Make listeners run asynchronously:

```php
<?php

namespace App\Listeners;

use NeoPhp\Queue\ShouldQueue;
use App\Events\OrderPlaced;

class SendOrderConfirmation implements ShouldQueue
{
    public int $tries = 3;
    public int $timeout = 60;
    
    public function handle(OrderPlaced $event): void
    {
        Mail::to($event->order->user->email)
            ->send(new OrderConfirmation($event->order));
    }
}
```

## Event Subscribers

Group multiple event listeners:

```php
<?php

namespace App\Listeners;

use NeoPhp\Events\Subscriber;
use App\Events\{UserRegistered, UserLoggedIn, UserLoggedOut};

class UserEventSubscriber implements Subscriber
{
    public function onUserRegistered(UserRegistered $event): void
    {
        logger()->info('User registered', ['user_id' => $event->user->id]);
    }
    
    public function onUserLoggedIn(UserLoggedIn $event): void
    {
        logger()->info('User logged in', ['user_id' => $event->user->id]);
    }
    
    public function onUserLoggedOut(UserLoggedOut $event): void
    {
        logger()->info('User logged out', ['user_id' => $event->user->id]);
    }
    
    public function subscribe(Dispatcher $events): array
    {
        return [
            UserRegistered::class => 'onUserRegistered',
            UserLoggedIn::class => 'onUserLoggedIn',
            UserLoggedOut::class => 'onUserLoggedOut',
        ];
    }
}
```

## Wildcard Listeners

Listen to multiple events with patterns:

```php
use NeoPhp\Events\Facades\Event;

Event::listen('user.*', function($event, $payload) {
    logger()->debug('User event', [
        'event' => $event,
        'data' => $payload
    ]);
});

// Triggered by:
event('user.registered', [$user]);
event('user.updated', [$user]);
event('user.deleted', [$user]);
```

## Stopping Event Propagation

```php
class CheckUserStatus
{
    public function handle(UserLoggedIn $event): bool
    {
        if (!$event->user->is_active) {
            // Stop propagation
            return false;
        }
        
        return true;
    }
}
```

## Event Discovery

Auto-discover events and listeners:

```php
<?php

namespace App\Providers;

use NeoPhp\Events\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
    
    protected function discoverEventsWithin(): array
    {
        return [
            $this->app->path('Listeners'),
        ];
    }
}
```

## Testing Events

```php
use NeoPhp\Events\Facades\Event;
use App\Events\UserRegistered;

public function test_user_registration_dispatches_event(): void
{
    Event::fake();
    
    $user = User::factory()->create();
    
    Event::assertDispatched(UserRegistered::class, function($event) use ($user) {
        return $event->user->id === $user->id;
    });
}

public function test_listener_handles_event(): void
{
    Event::fake();
    
    $listener = new SendWelcomeEmail();
    $event = new UserRegistered($user);
    
    $listener->handle($event);
    
    // Assert email sent
    Mail::assertSent(WelcomeEmail::class);
}
```

## Best Practices

### 1. Keep Events Simple

```php
// Good ✅
class OrderPlaced
{
    public function __construct(public Order $order) {}
}

// Bad ❌
class OrderPlaced
{
    public function __construct(
        public Order $order,
        public User $user,
        public array $items,
        public float $total,
        // Too many properties...
    ) {}
}
```

### 2. Use Listeners for Side Effects

```php
// Good ✅
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeEmail());
    }
}
```

### 3. Queue Heavy Listeners

```php
// Good ✅
class ProcessLargeFile implements ShouldQueue
{
    public function handle(FileUploaded $event): void
    {
        // Heavy processing
    }
}
```

### 4. Use Descriptive Event Names

```php
// Good ✅
OrderPlaced, PaymentProcessed, UserRegistered

// Bad ❌
Event1, OrderEvent, UserStuff
```

### 5. Document Event Properties

```php
/**
 * User registration event.
 *
 * @property User $user The registered user
 * @property string $source Registration source (web, api, mobile)
 */
class UserRegistered
{
    public function __construct(
        public User $user,
        public string $source
    ) {}
}
```

## Next Steps

- [Queue System](queue.md)
- [Logging](logging.md)
- [Task Scheduling](scheduling.md)
- [Broadcasting](broadcasting.md)
