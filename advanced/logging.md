# Logging

Record and monitor application activity with flexible logging.

## Configuration

Configure logging in `config/logging.php`:

```php
return [
    'default' => env('LOG_CHANNEL', 'stack'),
    
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/neophp.log'),
            'level' => 'debug',
            'days' => 14,
        ],
        
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/neophp.log'),
            'level' => 'debug',
        ],
        
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'level' => 'critical',
        ],
        
        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],
        
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],
    ],
];
```

## Basic Usage

### Log Levels

```php
use NeoPhp\Log\Facades\Log;

// Emergency: system is unusable
Log::emergency('System is down!');

// Alert: action must be taken immediately
Log::alert('Database connection lost');

// Critical: critical conditions
Log::critical('Application component unavailable');

// Error: error conditions
Log::error('Failed to process payment', ['order_id' => 123]);

// Warning: warning conditions
Log::warning('Disk space low', ['available' => '5GB']);

// Notice: normal but significant
Log::notice('User logged in', ['user_id' => 456]);

// Info: informational messages
Log::info('Order created', ['order_id' => 789]);

// Debug: debug-level messages
Log::debug('Cache hit', ['key' => 'user:123']);
```

### Contextual Information

```php
Log::info('User action', [
    'user_id' => auth()->id(),
    'action' => 'profile_update',
    'ip' => request()->ip(),
    'timestamp' => now()
]);
```

## Writing to Specific Channels

```php
// Single channel
Log::channel('slack')->critical('Payment processing failed!');

// Multiple channels
Log::stack(['daily', 'slack'])->error('Database error');
```

## Complete Examples

### User Activity Logging

```php
<?php

namespace App\Services;

use NeoPhp\Log\Facades\Log;

class UserActivityLogger
{
    public function logLogin(User $user, string $ip): void
    {
        Log::channel('daily')->info('User logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $ip,
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    public function logFailedLogin(string $email, string $ip): void
    {
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $email,
            'ip' => $ip,
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    public function logPasswordChange(User $user): void
    {
        Log::channel('security')->notice('Password changed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip()
        ]);
    }
    
    public function logAccountDeletion(User $user, string $reason): void
    {
        Log::channel('daily')->alert('Account deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'reason' => $reason,
            'deleted_at' => now()->toIso8601String()
        ]);
    }
}
```

### API Request Logging

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Log\Facades\Log;

class LogApiRequests
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        Log::channel('api')->info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'status' => $response->getStatusCode(),
            'duration' => round($duration * 1000, 2) . 'ms',
            'memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB'
        ]);
        
        return $response;
    }
}
```

### Database Query Logging

```php
<?php

namespace App\Providers;

use NeoPhp\Database\Events\QueryExecuted;
use NeoPhp\Log\Facades\Log;

class AppServiceProvider
{
    public function boot(): void
    {
        DB::listen(function(QueryExecuted $query) {
            if ($query->time > 1000) { // Log slow queries
                Log::channel('performance')->warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms'
                ]);
            }
            
            if (config('app.debug')) {
                Log::channel('daily')->debug('Query executed', [
                    'sql' => $query->sql,
                    'time' => $query->time . 'ms'
                ]);
            }
        });
    }
}
```

### Payment Transaction Logging

```php
<?php

namespace App\Services;

use NeoPhp\Log\Facades\Log;

class PaymentLogger
{
    public function logPaymentAttempt(Order $order, string $method): void
    {
        Log::channel('payments')->info('Payment attempt', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total,
            'method' => $method
        ]);
    }
    
    public function logPaymentSuccess(Order $order, string $transactionId): void
    {
        Log::channel('payments')->info('Payment successful', [
            'order_id' => $order->id,
            'transaction_id' => $transactionId,
            'amount' => $order->total
        ]);
    }
    
    public function logPaymentFailure(Order $order, string $error): void
    {
        Log::channel('payments')->error('Payment failed', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total,
            'error' => $error
        ]);
    }
    
    public function logRefund(Order $order, float $amount, string $reason): void
    {
        Log::channel('payments')->warning('Refund processed', [
            'order_id' => $order->id,
            'amount' => $amount,
            'reason' => $reason
        ]);
    }
}
```

### Exception Logging

```php
<?php

namespace App\Exceptions;

use Exception;
use NeoPhp\Log\Facades\Log;

class Handler
{
    public function report(Exception $exception): void
    {
        if ($this->shouldReport($exception)) {
            Log::error('Exception occurred', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'url' => request()->fullUrl(),
                'user_id' => auth()->id()
            ]);
        }
    }
    
    private function shouldReport(Exception $exception): bool
    {
        return !in_array(get_class($exception), [
            ValidationException::class,
            AuthenticationException::class,
        ]);
    }
}
```

## Custom Log Channels

### Email Channel

```php
<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use NeoPhp\Mail\Facades\Mail;

class EmailLogHandler extends AbstractProcessingHandler
{
    protected function write(array $record): void
    {
        Mail::to(config('logging.email.to'))
            ->send(new LogAlert($record));
    }
}

// Register in logging.php
'email' => [
    'driver' => 'custom',
    'via' => App\Logging\EmailLogHandler::class,
    'level' => 'critical',
],
```

### Database Channel

```php
<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use App\Models\Log as LogModel;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    protected function write(array $record): void
    {
        LogModel::create([
            'level' => $record['level_name'],
            'message' => $record['message'],
            'context' => json_encode($record['context']),
            'created_at' => $record['datetime']
        ]);
    }
}

// Register in logging.php
'database' => [
    'driver' => 'custom',
    'via' => App\Logging\DatabaseLogHandler::class,
    'level' => 'debug',
],
```

## Log Formatting

### Custom Formatter

```php
<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class CustomFormatter extends LineFormatter
{
    public function format(array $record): string
    {
        return sprintf(
            "[%s] %s.%s: %s %s\n",
            $record['datetime']->format('Y-m-d H:i:s'),
            $record['channel'],
            $record['level_name'],
            $record['message'],
            json_encode($record['context'])
        );
    }
}

// Use in logging.php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/neophp.log'),
    'level' => 'debug',
    'formatter' => App\Logging\CustomFormatter::class,
],
```

## Log Rotation

### Daily Rotation

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/neophp.log'),
    'level' => 'debug',
    'days' => 14, // Keep 14 days
],
```

### Size-based Rotation

```php
'rotating' => [
    'driver' => 'rotating',
    'path' => storage_path('logs/neophp.log'),
    'level' => 'debug',
    'max_files' => 10,
],
```

## Contextual Logging

### Log Context Service

```php
<?php

namespace App\Services;

use NeoPhp\Log\Facades\Log;

class ContextLogger
{
    private array $context = [];
    
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }
    
    public function log(string $level, string $message, array $extra = []): void
    {
        $context = array_merge($this->context, $extra, [
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->header('X-Request-ID'),
        ]);
        
        Log::$level($message, $context);
        
        // Clear context after logging
        $this->context = [];
    }
}

// Usage
$logger = new ContextLogger();

$logger->withContext([
    'user_id' => auth()->id(),
    'ip' => request()->ip()
])->log('info', 'User action', ['action' => 'update_profile']);
```

## Performance Monitoring

```php
<?php

namespace App\Services;

use NeoPhp\Log\Facades\Log;

class PerformanceMonitor
{
    private float $startTime;
    private int $startMemory;
    
    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }
    
    public function end(string $operation): void
    {
        $duration = microtime(true) - $this->startTime;
        $memory = memory_get_usage() - $this->startMemory;
        
        Log::channel('performance')->info("Performance: {$operation}", [
            'duration' => round($duration * 1000, 2) . 'ms',
            'memory' => round($memory / 1024, 2) . 'KB',
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB'
        ]);
    }
}

// Usage
$monitor = new PerformanceMonitor();
$monitor->start();

// ... perform operation

$monitor->end('Export users to CSV');
```

## Security Logging

```php
<?php

namespace App\Services;

use NeoPhp\Log\Facades\Log;

class SecurityLogger
{
    public function logSuspiciousActivity(string $activity, array $context = []): void
    {
        Log::channel('security')->warning('Suspicious activity detected', array_merge([
            'activity' => $activity,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String()
        ], $context));
    }
    
    public function logDataAccess(string $resource, array $data): void
    {
        Log::channel('audit')->info('Data accessed', [
            'resource' => $resource,
            'user_id' => auth()->id(),
            'data' => $data,
            'ip' => request()->ip()
        ]);
    }
    
    public function logPermissionDenied(string $permission): void
    {
        Log::channel('security')->notice('Permission denied', [
            'permission' => $permission,
            'user_id' => auth()->id(),
            'url' => request()->fullUrl()
        ]);
    }
}
```

## Best Practices

### 1. Use Appropriate Log Levels

```php
// Good ✅
Log::debug('Cache hit', ['key' => 'user:123']);
Log::info('Order created', ['order_id' => 789]);
Log::warning('Low disk space', ['available' => '5GB']);
Log::error('Payment failed', ['error' => $e->getMessage()]);
Log::critical('Database connection lost');

// Bad ❌
Log::info('System is down!'); // Should be emergency
Log::error('User logged in'); // Should be info
```

### 2. Include Context

```php
// Good ✅
Log::error('Failed to send email', [
    'user_id' => $user->id,
    'email' => $user->email,
    'error' => $exception->getMessage()
]);

// Bad ❌
Log::error('Failed to send email');
```

### 3. Don't Log Sensitive Data

```php
// Good ✅
Log::info('Payment processed', [
    'order_id' => $order->id,
    'amount' => $order->total,
    'card_last4' => substr($card, -4)
]);

// Bad ❌
Log::info('Payment processed', [
    'card_number' => $cardNumber,
    'cvv' => $cvv
]);
```

### 4. Use Structured Logging

```php
// Good ✅
Log::info('Order created', [
    'order_id' => $order->id,
    'user_id' => $user->id,
    'total' => $order->total,
    'items_count' => $order->items->count()
]);

// Bad ❌
Log::info("Order {$order->id} created by user {$user->id} with total {$order->total}");
```

### 5. Log Exceptions Properly

```php
// Good ✅
try {
    // code
} catch (Exception $e) {
    Log::error('Operation failed', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

// Bad ❌
catch (Exception $e) {
    Log::error($e->getMessage());
}
```

## Next Steps

- [Events](events.md)
- [Queue System](queue.md)
- [Security](security.md)
- [Monitoring](monitoring.md)
