# Queue System

Process time-consuming tasks asynchronously using queues.

## Configuration

Configure queues in `config/queue.php`:

```php
return [
    'default' => env('QUEUE_CONNECTION', 'redis'),
    
    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],
        
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'retry_after' => 90,
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ],
    
    'failed' => [
        'driver' => 'database',
        'table' => 'failed_jobs',
    ],
];
```

## Creating Jobs

### Basic Job

```php
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;

class SendEmailJob extends Job
{
    public function __construct(
        private string $email,
        private string $message
    ) {}
    
    public function handle(): void
    {
        // Send email
        mail($this->email, 'Subject', $this->message);
    }
}
```

### Job with Dependencies

```php
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;
use App\Services\{MailService, LogService};

class SendWelcomeEmailJob extends Job
{
    public function __construct(private int $userId) {}
    
    public function handle(MailService $mail, LogService $log): void
    {
        $user = User::findOrFail($this->userId);
        
        $mail->to($user->email)->send(new WelcomeEmail($user));
        
        $log->info('Welcome email sent', ['user_id' => $this->userId]);
    }
}
```

## Dispatching Jobs

### Basic Dispatch

```php
use App\Jobs\SendEmailJob;

// Dispatch to queue
SendEmailJob::dispatch('user@example.com', 'Hello!');

// Dispatch immediately (synchronous)
SendEmailJob::dispatchSync('user@example.com', 'Hello!');

// Dispatch after delay
SendEmailJob::dispatch('user@example.com', 'Hello!')
    ->delay(now()->addMinutes(10));
```

### Advanced Dispatch

```php
// Specific queue
SendEmailJob::dispatch($email, $message)
    ->onQueue('emails');

// Specific connection
SendEmailJob::dispatch($email, $message)
    ->onConnection('redis');

// With delay
SendEmailJob::dispatch($email, $message)
    ->delay(now()->addHours(2));

// Chain jobs
SendEmailJob::dispatch($email, $message)
    ->chain([
        new UpdateUserStatsJob($userId),
        new SendFollowUpEmailJob($userId),
    ]);
```

## Job Chaining

Execute jobs in sequence:

```php
use NeoPhp\Queue\Facades\Queue;

Queue::chain([
    new ProcessOrderJob($orderId),
    new ChargePaymentJob($orderId),
    new SendConfirmationJob($orderId),
    new UpdateInventoryJob($orderId),
])->dispatch();
```

## Job Batching

Group multiple jobs:

```php
use NeoPhp\Queue\Facades\Bus;

$batch = Bus::batch([
    new ProcessImage($image1),
    new ProcessImage($image2),
    new ProcessImage($image3),
])->then(function(Batch $batch) {
    // All jobs completed
})->catch(function(Batch $batch, Throwable $e) {
    // First job failure
})->finally(function(Batch $batch) {
    // Batch finished
})->dispatch();

// Check batch status
$batch = Bus::findBatch($batchId);
echo $batch->progress(); // 75%
```

## Complete Examples

### Email Queue

```php
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;
use App\Mail\OrderConfirmation;
use NeoPhp\Mail\Facades\Mail;

class SendOrderConfirmationJob extends Job
{
    public int $tries = 3;
    public int $timeout = 60;
    
    public function __construct(
        private int $orderId,
        private string $email
    ) {}
    
    public function handle(): void
    {
        $order = Order::with(['items.product', 'user'])
            ->findOrFail($this->orderId);
        
        Mail::to($this->email)
            ->send(new OrderConfirmation($order));
        
        // Update order
        $order->update(['confirmation_sent_at' => now()]);
    }
    
    public function failed(Throwable $exception): void
    {
        // Handle job failure
        logger()->error('Failed to send order confirmation', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage()
        ]);
    }
}

// Dispatch
SendOrderConfirmationJob::dispatch($order->id, $order->user->email);
```

### Image Processing

```php
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;
use Intervention\Image\Facades\Image;
use NeoPhp\Storage\Facades\Storage;

class ProcessImageJob extends Job
{
    public function __construct(
        private string $imagePath,
        private array $sizes = ['thumbnail', 'medium', 'large']
    ) {}
    
    public function handle(): void
    {
        $image = Storage::get($this->imagePath);
        
        foreach ($this->sizes as $size) {
            $this->processSize($image, $size);
        }
    }
    
    private function processSize(string $image, string $size): void
    {
        $dimensions = $this->getDimensions($size);
        
        $processed = Image::make($image)
            ->fit($dimensions['width'], $dimensions['height'])
            ->encode('jpg', 90);
        
        $path = $this->getOutputPath($size);
        Storage::put($path, $processed);
    }
    
    private function getDimensions(string $size): array
    {
        return match($size) {
            'thumbnail' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 600, 'height' => 400],
            'large' => ['width' => 1200, 'height' => 800],
        };
    }
}

// Dispatch batch
Bus::batch([
    new ProcessImageJob('uploads/image1.jpg'),
    new ProcessImageJob('uploads/image2.jpg'),
    new ProcessImageJob('uploads/image3.jpg'),
])->dispatch();
```

### Export Data

```php
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;
use App\Services\ExportService;

class ExportUsersJob extends Job
{
    public function __construct(
        private int $userId,
        private array $filters = []
    ) {}
    
    public function handle(ExportService $export): void
    {
        // Build query
        $query = User::query();
        
        foreach ($this->filters as $field => $value) {
            $query->where($field, $value);
        }
        
        // Export to CSV
        $filePath = $export->toCsv($query->get(), 'users');
        
        // Notify user
        $user = User::find($this->userId);
        Mail::to($user->email)->send(new ExportReady($filePath));
    }
}

// Dispatch
ExportUsersJob::dispatch(auth()->id(), [
    'status' => 'active',
    'created_at' => ['>=', '2024-01-01']
])->delay(now()->addSeconds(10));
```

### Report Generation

```php
<?php

namespace App\Jobs;

use NeoPhp\Queue\Job;
use App\Services\ReportService;

class GenerateSalesReportJob extends Job
{
    public function __construct(
        private string $startDate,
        private string $endDate,
        private int $userId
    ) {}
    
    public function handle(ReportService $reports): void
    {
        $data = $reports->getSalesData($this->startDate, $this->endDate);
        
        $pdf = $reports->generatePdf($data);
        
        $filePath = "reports/sales_{$this->startDate}_{$this->endDate}.pdf";
        Storage::put($filePath, $pdf);
        
        // Notify user
        $user = User::find($this->userId);
        Mail::to($user->email)->send(new ReportReady($filePath));
    }
}

// Dispatch
GenerateSalesReportJob::dispatch(
    '2024-01-01',
    '2024-12-31',
    auth()->id()
);
```

## Queue Workers

### Starting Worker

```bash
# Start worker
php neo queue:work

# Specific queue
php neo queue:work --queue=emails

# Specific connection
php neo queue:work redis --queue=emails

# Process single job
php neo queue:work --once

# Stop after processing
php neo queue:work --stop-when-empty
```

### Worker Options

```bash
# Max jobs before restart
php neo queue:work --max-jobs=1000

# Max seconds before restart
php neo queue:work --max-time=3600

# Memory limit
php neo queue:work --memory=512

# Sleep when no jobs
php neo queue:work --sleep=3

# Number of attempts
php neo queue:work --tries=3

# Timeout per job
php neo queue:work --timeout=60
```

## Failed Jobs

### Handling Failures

```php
public function failed(Throwable $exception): void
{
    // Notify admin
    Mail::to('admin@example.com')->send(
        new JobFailedNotification($this, $exception)
    );
    
    // Log error
    logger()->error('Job failed', [
        'job' => static::class,
        'error' => $exception->getMessage()
    ]);
}
```

### Retry Failed Jobs

```bash
# List failed jobs
php neo queue:failed

# Retry specific job
php neo queue:retry 5

# Retry all failed jobs
php neo queue:retry all

# Delete failed job
php neo queue:forget 5

# Flush all failed jobs
php neo queue:flush
```

## Job Middleware

### Rate Limiting

```php
<?php

namespace App\Jobs\Middleware;

class RateLimited
{
    public function handle($job, $next): void
    {
        $key = 'job:' . get_class($job);
        
        if (Cache::has($key)) {
            // Rate limit exceeded, release job back to queue
            $job->release(60);
            return;
        }
        
        Cache::put($key, true, 60);
        
        $next($job);
    }
}

// Use middleware
class SendEmailJob extends Job
{
    public function middleware(): array
    {
        return [new RateLimited];
    }
}
```

### Job Logging

```php
<?php

namespace App\Jobs\Middleware;

class LogJob
{
    public function handle($job, $next): void
    {
        $start = microtime(true);
        
        logger()->info('Job started', [
            'job' => get_class($job),
            'payload' => serialize($job)
        ]);
        
        $next($job);
        
        $duration = microtime(true) - $start;
        
        logger()->info('Job completed', [
            'job' => get_class($job),
            'duration' => $duration
        ]);
    }
}
```

## Job Events

```php
use NeoPhp\Queue\Events\{JobProcessing, JobProcessed, JobFailed};

// Before processing
$events->listen(JobProcessing::class, function($event) {
    logger()->debug('Processing job', ['job' => $event->job]);
});

// After processing
$events->listen(JobProcessed::class, function($event) {
    logger()->info('Job completed', ['job' => $event->job]);
});

// On failure
$events->listen(JobFailed::class, function($event) {
    logger()->error('Job failed', [
        'job' => $event->job,
        'exception' => $event->exception
    ]);
});
```

## Best Practices

### 1. Keep Jobs Small

```php
// Good ✅
class SendEmailJob extends Job
{
    public function handle(MailService $mail): void
    {
        $mail->send($this->email, $this->message);
    }
}

// Bad ❌
class ProcessOrderJob extends Job
{
    public function handle(): void
    {
        // Too many responsibilities
        $this->validateOrder();
        $this->chargePayment();
        $this->updateInventory();
        $this->sendEmails();
        $this->notifyVendor();
    }
}
```

### 2. Use Job Chaining

```php
// Good ✅
ProcessOrderJob::dispatch($orderId)
    ->chain([
        new ChargePaymentJob($orderId),
        new SendConfirmationJob($orderId),
    ]);
```

### 3. Handle Failures Gracefully

```php
// Good ✅
public int $tries = 3;

public function failed(Throwable $exception): void
{
    // Notify, log, cleanup
}
```

### 4. Set Appropriate Timeouts

```php
// Good ✅
public int $timeout = 120; // 2 minutes for API call
```

### 5. Use Specific Queues

```php
// Good ✅
SendEmailJob::dispatch()->onQueue('emails');
ProcessImageJob::dispatch()->onQueue('images');
GenerateReportJob::dispatch()->onQueue('reports');
```

## Next Steps

- [Caching](caching.md)
- [Events](events.md)
- [Logging](logging.md)
- [Task Scheduling](scheduling.md)
