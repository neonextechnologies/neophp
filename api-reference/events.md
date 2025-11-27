# Events & Listeners

Complete reference for event system.

## Event

Event dispatcher and manager.

### Dispatching Events

#### `dispatch($event, $payload = [], $halt = false)`

Dispatch event.

```php
Event::dispatch(new UserRegistered($user));
Event::dispatch('user.login', ['user' => $user]);
```

#### `dispatchIf($condition, $event, $payload = [])`

Dispatch if condition true.

```php
Event::dispatchIf($user->verified, new AccountVerified($user));
```

#### `dispatchUnless($condition, $event, $payload = [])`

Dispatch unless condition true.

```php
Event::dispatchUnless($user->guest(), new UserActivity($user));
```

### Listening to Events

#### `listen($events, $listener)`

Register listener.

```php
Event::listen(UserRegistered::class, SendWelcomeEmail::class);

Event::listen('user.*', function($event, $data) {
    Log::info('User event', ['event' => $event, 'data' => $data]);
});
```

#### `subscribe($subscriber)`

Register subscriber.

```php
Event::subscribe(UserEventSubscriber::class);
```

### Event Methods

#### `forget($event)`

Remove listeners.

```php
Event::forget(UserRegistered::class);
```

#### `flush($event)`

Remove all listeners.

```php
Event::flush('user.login');
```

#### `hasListeners($event)`

Check if has listeners.

```php
if (Event::hasListeners(OrderCreated::class)) {
    // Has listeners
}
```

---

## Creating Events

### Basic Event

```php
<?php

namespace App\Events;

class UserRegistered
{
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
}
```

### Broadcast Event

```php
<?php

namespace App\Events;

use Neo\Broadcasting\InteractsWithSockets;
use Neo\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public $message;
    
    public function __construct($message)
    {
        $this->message = $message;
    }
    
    public function broadcastOn()
    {
        return ['chat.' . $this->message->conversation_id];
    }
    
    public function broadcastAs()
    {
        return 'message.sent';
    }
    
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'user' => $this->message->user->name
        ];
    }
}
```

---

## Creating Listeners

### Basic Listener

```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
}
```

### Listener with Dependencies

```php
class UpdateUserStats
{
    protected $analytics;
    
    public function __construct(Analytics $analytics)
    {
        $this->analytics = $analytics;
    }
    
    public function handle(UserLoggedIn $event)
    {
        $this->analytics->trackLogin($event->user);
    }
}
```

### Queued Listener

```php
<?php

namespace App\Listeners;

use Neo\Queue\InteractsWithQueue;
use Neo\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;
    
    public $queue = 'emails';
    public $delay = 60;
    
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
    
    public function failed(UserRegistered $event, $exception)
    {
        Log::error('Failed to send welcome email', [
            'user' => $event->user->id,
            'error' => $exception->getMessage()
        ]);
    }
}
```

### Conditional Queue

```php
class SendNotification implements ShouldQueue
{
    public function shouldQueue(OrderShipped $event)
    {
        return $event->order->user->wants_notifications;
    }
    
    public function handle(OrderShipped $event)
    {
        // Send notification
    }
}
```

---

## Event Subscribers

### Creating Subscriber

```php
<?php

namespace App\Listeners;

class UserEventSubscriber
{
    public function handleUserLogin($event)
    {
        Log::info('User logged in', ['user' => $event->user->id]);
    }
    
    public function handleUserLogout($event)
    {
        Log::info('User logged out', ['user' => $event->user->id]);
    }
    
    public function handleUserRegistered($event)
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
    
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\UserLoggedIn',
            [UserEventSubscriber::class, 'handleUserLogin']
        );
        
        $events->listen(
            'App\Events\UserLoggedOut',
            [UserEventSubscriber::class, 'handleUserLogout']
        );
        
        $events->listen(
            'App\Events\UserRegistered',
            [UserEventSubscriber::class, 'handleUserRegistered']
        );
    }
}
```

### Registering Subscriber

```php
// In EventServiceProvider
protected $subscribe = [
    UserEventSubscriber::class,
    OrderEventSubscriber::class,
];
```

---

## Practical Examples

### User Registration Flow

```php
// Event
class UserRegistered
{
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
}

// Listeners
class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
}

class CreateUserProfile
{
    public function handle(UserRegistered $event)
    {
        Profile::create([
            'user_id' => $event->user->id,
            'bio' => '',
            'avatar' => 'default.png'
        ]);
    }
}

class TrackUserRegistration
{
    public function handle(UserRegistered $event)
    {
        Analytics::track('user_registered', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'timestamp' => now()
        ]);
    }
}

// Dispatch
public function register(Request $request)
{
    $user = User::create($request->validated());
    
    Event::dispatch(new UserRegistered($user));
    
    return redirect('/dashboard');
}
```

### Order Processing

```php
// Event
class OrderCreated
{
    public $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
}

// Listeners
class ProcessPayment implements ShouldQueue
{
    public function handle(OrderCreated $event)
    {
        PaymentService::charge($event->order);
    }
}

class UpdateInventory implements ShouldQueue
{
    public function handle(OrderCreated $event)
    {
        foreach ($event->order->items as $item) {
            Product::find($item->product_id)->decrement('stock', $item->quantity);
        }
    }
}

class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderCreated $event)
    {
        Mail::to($event->order->user->email)
            ->send(new OrderConfirmationEmail($event->order));
    }
}

class NotifyWarehouse implements ShouldQueue
{
    public function handle(OrderCreated $event)
    {
        Notification::send(
            User::warehouse(),
            new NewOrderNotification($event->order)
        );
    }
}
```

### File Upload Processing

```php
// Event
class FileUploaded
{
    public $file;
    public $user;
    
    public function __construct($file, $user)
    {
        $this->file = $file;
        $this->user = $user;
    }
}

// Listeners
class ScanFileForVirus implements ShouldQueue
{
    public function handle(FileUploaded $event)
    {
        $scanner = new VirusScanner();
        
        if ($scanner->scan($event->file->path)) {
            $event->file->update(['scanned' => true, 'safe' => true]);
        } else {
            $event->file->delete();
            Event::dispatch(new VirusDetected($event->file, $event->user));
        }
    }
}

class GenerateFileThumbnail implements ShouldQueue
{
    public function handle(FileUploaded $event)
    {
        if ($event->file->isImage()) {
            ImageProcessor::createThumbnail($event->file);
        }
    }
}

class UpdateUserStorage
{
    public function handle(FileUploaded $event)
    {
        $event->user->increment('storage_used', $event->file->size);
    }
}
```

### Model Events

```php
class Post extends Model
{
    protected $dispatchesEvents = [
        'created' => PostCreated::class,
        'updated' => PostUpdated::class,
        'deleted' => PostDeleted::class,
    ];
    
    protected static function booted()
    {
        static::creating(function($post) {
            $post->slug = Str::slug($post->title);
        });
        
        static::created(function($post) {
            Cache::forget('posts.all');
        });
        
        static::deleting(function($post) {
            $post->comments()->delete();
        });
    }
}

// Listener
class ClearPostCache
{
    public function handle($event)
    {
        Cache::tags(['posts'])->flush();
    }
}
```

---

## Wildcard Listeners

```php
Event::listen('user.*', function($event, $data) {
    Log::info('User event occurred', [
        'event' => $event,
        'data' => $data
    ]);
});

Event::listen('order.*', function($event, $data) {
    Analytics::track($event, $data);
});
```

---

## Stopping Event Propagation

```php
Event::listen(UserRegistered::class, function($event) {
    // Check if user is spam
    if ($this->isSpam($event->user)) {
        return false; // Stop propagation
    }
});
```

---

## Event Discovery

### Auto-Discovery

Events are automatically discovered by scanning the `App\Listeners` directory.

```php
// EventServiceProvider
public function shouldDiscoverEvents()
{
    return true;
}

public function discoverEventsWithin()
{
    return [
        $this->app->path('Listeners'),
        $this->app->path('Modules/*/Listeners'),
    ];
}
```

---

## Testing Events

### Fake Events

```php
use Neo\Support\Facades\Event;

public function test_user_registration()
{
    Event::fake();
    
    $user = User::factory()->create();
    
    Event::assertDispatched(UserRegistered::class, function($event) use ($user) {
        return $event->user->id === $user->id;
    });
    
    Event::assertNotDispatched(UserDeleted::class);
}
```

### Fake Specific Events

```php
Event::fake([
    UserRegistered::class,
    UserLoggedIn::class
]);

// Other events will dispatch normally
```

### Assert Event Times

```php
Event::fake();

// Actions...

Event::assertDispatched(OrderCreated::class, 3); // Dispatched 3 times
```

---

## Best Practices

### Event Naming

```php
// Good - descriptive and past tense
class UserRegistered extends Event {}
class OrderShipped extends Event {}
class PaymentProcessed extends Event {}

// Bad - vague or present tense
class User extends Event {}
class ShipOrder extends Event {}
class Payment extends Event {}
```

### Listener Organization

```php
// Group by feature
App/
  Listeners/
    User/
      SendWelcomeEmail.php
      CreateUserProfile.php
      TrackRegistration.php
    Order/
      ProcessPayment.php
      UpdateInventory.php
      SendConfirmation.php
```

### Queue Heavy Operations

```php
// Queue time-consuming tasks
class SendEmailNotification implements ShouldQueue
{
    public $queue = 'emails';
    
    public function handle($event)
    {
        Mail::send(...);
    }
}

// Execute quickly in sync
class UpdateCache
{
    public function handle($event)
    {
        Cache::forget('key');
    }
}
```

### Event Documentation

```php
/**
 * Fired when a new user registers
 * 
 * Listeners:
 * - SendWelcomeEmail: Sends welcome email to user
 * - CreateUserProfile: Creates default profile
 * - TrackRegistration: Logs analytics event
 */
class UserRegistered extends Event
{
    public User $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
```

---

## Next Steps

- [Mail Classes](mail.md)
- [Notification Classes](notification.md)
- [Broadcasting](../advanced/broadcasting.md)
- [Testing Events](../tutorials/testing-guide.md)
