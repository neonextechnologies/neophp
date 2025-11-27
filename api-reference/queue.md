# Queue Classes

Complete reference for queue and job processing.

## Queue

Queue manager and facade.

### Dispatching Jobs

#### `push($job, $data = '', $queue = null)`

Push job to queue.

```php
Queue::push(new SendEmailJob($user));
Queue::push(new ProcessImageJob($image), '', 'images');
```

#### `later($delay, $job, $data = '', $queue = null)`

Push job with delay.

```php
Queue::later(60, new SendReminderJob($user));
Queue::later(now()->addMinutes(10), new CleanupJob());
```

#### `bulk($jobs, $data = '', $queue = null)`

Push multiple jobs.

```php
$jobs = [
    new SendEmailJob($user1),
    new SendEmailJob($user2),
    new SendEmailJob($user3)
];

Queue::bulk($jobs);
```

### Queue Operations

#### `size($queue = null)`

Get queue size.

```php
$pending = Queue::size();
$emailQueue = Queue::size('emails');
```

#### `connection($name = null)`

Get queue connection.

```php
$redis = Queue::connection('redis');
$sync = Queue::connection('sync');
```

---

## Job

Base job class for queue workers.

### Properties

```php
public $queue = 'default';      // Queue name
public $delay = 0;              // Delay in seconds
public $tries = 3;              // Max attempts
public $timeout = 60;           // Timeout in seconds
public $maxExceptions = 3;      // Max exceptions before failing
```

### Methods

#### `dispatch(...$arguments)`

Dispatch job.

```php
SendEmailJob::dispatch($user, $message);
```

#### `dispatchIf($condition, ...$arguments)`

Dispatch if condition is true.

```php
SendEmailJob::dispatchIf($user->wants_notifications, $user, $message);
```

#### `dispatchUnless($condition, ...$arguments)`

Dispatch unless condition is true.

```php
SendEmailJob::dispatchUnless($user->unsubscribed, $user, $message);
```

#### `dispatchSync(...$arguments)`

Dispatch synchronously.

```php
ProcessImageJob::dispatchSync($image);
```

#### `dispatchAfterResponse(...$arguments)`

Dispatch after response sent.

```php
LogActivityJob::dispatchAfterResponse($activity);
```

#### `handle()`

Execute job.

```php
public function handle()
{
    // Job logic
}
```

#### `failed(\Exception $exception)`

Handle job failure.

```php
public function failed(\Exception $exception)
{
    // Cleanup or notification
}
```

#### `release($delay = 0)`

Release job back to queue.

```php
public function handle()
{
    if (!$this->canProcess()) {
        $this->release(60); // Retry in 60 seconds
    }
}
```

#### `delete()`

Delete job from queue.

```php
public function handle()
{
    if ($this->shouldSkip()) {
        $this->delete();
    }
}
```

#### `fail($exception = null)`

Manually fail job.

```php
public function handle()
{
    if ($this->isInvalid()) {
        $this->fail(new InvalidDataException());
    }
}
```

---

## Dispatchable Jobs

### Basic Job

```php
<?php

namespace App\Jobs;

use Neo\Queue\Job;
use Neo\Queue\InteractsWithQueue;

class SendEmailJob extends Job
{
    use InteractsWithQueue;
    
    protected $user;
    protected $message;
    
    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }
    
    public function handle()
    {
        Mail::to($this->user->email)->send(new CustomEmail($this->message));
    }
    
    public function failed(\Exception $exception)
    {
        Log::error('Failed to send email', [
            'user' => $this->user->id,
            'error' => $exception->getMessage()
        ]);
    }
}
```

### Job with Dependencies

```php
class ProcessOrderJob extends Job
{
    protected $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
    
    public function handle(PaymentService $payment, InventoryService $inventory)
    {
        $payment->charge($this->order);
        $inventory->decrementStock($this->order->items);
    }
}
```

---

## Job Chaining

### Chain Jobs

```php
use Neo\Queue\Chain;

Chain::create([
    new ProcessOrderJob($order),
    new SendInvoiceJob($order),
    new UpdateInventoryJob($order),
    new NotifyAdminJob($order)
])->dispatch();
```

### Chain with Callback

```php
Chain::create([
    new Job1(),
    new Job2(),
    new Job3()
])
->then(function() {
    Log::info('All jobs completed');
})
->catch(function(\Exception $e) {
    Log::error('Chain failed', ['error' => $e->getMessage()]);
})
->dispatch();
```

---

## Job Batching

### Create Batch

```php
use Neo\Queue\Batch;

$batch = Batch::make([
    new ProcessUserJob($user1),
    new ProcessUserJob($user2),
    new ProcessUserJob($user3)
])
->name('Process Users')
->allowFailures()
->then(function(Batch $batch) {
    // All jobs completed
})
->catch(function(Batch $batch, \Exception $e) {
    // First batch job failure
})
->finally(function(Batch $batch) {
    // Batch finished executing
})
->dispatch();
```

### Batch Methods

```php
// Check batch status
if ($batch->finished()) {
    // All jobs completed
}

if ($batch->cancelled()) {
    // Batch was cancelled
}

// Cancel batch
$batch->cancel();

// Add more jobs to batch
$batch->add([
    new ProcessUserJob($user4),
    new ProcessUserJob($user5)
]);

// Get batch info
$total = $batch->totalJobs;
$pending = $batch->pendingJobs;
$failed = $batch->failedJobs;
$progress = $batch->progress();
```

---

## Delayed Dispatching

### Delay Methods

```php
// Delay in seconds
SendEmailJob::dispatch($user)->delay(60);

// Delay with Carbon
SendEmailJob::dispatch($user)->delay(now()->addMinutes(10));

// Delay until specific time
SendEmailJob::dispatch($user)->delay(now()->addDay());
```

---

## Queue Configuration

### Queue Names

```php
// Dispatch to specific queue
SendEmailJob::dispatch($user)->onQueue('emails');
ProcessImageJob::dispatch($image)->onQueue('images');
```

### Queue Connections

```php
// Dispatch to specific connection
SendEmailJob::dispatch($user)->onConnection('redis');
```

### Job Priority

```php
// Set job priority (lower = higher priority)
SendEmailJob::dispatch($user)->priority(1);
ImportantJob::dispatch()->priority(0);
```

---

## Job Middleware

### Rate Limiting

```php
<?php

namespace App\Jobs;

use Neo\Queue\Middleware\RateLimited;

class ProcessApiJob extends Job
{
    public function middleware()
    {
        return [new RateLimited('api', 10, 60)]; // 10 jobs per 60 seconds
    }
    
    public function handle()
    {
        // Make API call
    }
}
```

### Throttling

```php
use Neo\Queue\Middleware\ThrottlesExceptions;

class ProcessExternalJob extends Job
{
    public function middleware()
    {
        return [
            (new ThrottlesExceptions(3, 5))->backoff(60)
        ];
    }
}
```

### Custom Middleware

```php
class LogJobMiddleware
{
    public function handle($job, $next)
    {
        Log::info('Job started', ['job' => get_class($job)]);
        
        $result = $next($job);
        
        Log::info('Job finished', ['job' => get_class($job)]);
        
        return $result;
    }
}

// Use in job
public function middleware()
{
    return [new LogJobMiddleware()];
}
```

---

## Failed Jobs

### Handling Failures

```php
class SendEmailJob extends Job
{
    public $tries = 3;
    public $maxExceptions = 2;
    public $backoff = [60, 120, 300]; // Backoff in seconds
    
    public function failed(\Exception $exception)
    {
        // Send notification
        Notification::send(
            User::admins(),
            new JobFailedNotification($this, $exception)
        );
        
        // Log details
        Log::error('Email job failed', [
            'user_id' => $this->user->id,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);
    }
}
```

### Retry Failed Jobs

```php
// Retry specific failed job
Queue::retry($failedJobId);

// Retry all failed jobs
Queue::retryAll();

// Flush failed jobs
Queue::flushFailed();
```

---

## Job Events

### Listening to Job Events

```php
Queue::before(function($event) {
    Log::info('Job starting', [
        'job' => $event->job->resolveName(),
        'queue' => $event->queue
    ]);
});

Queue::after(function($event) {
    Log::info('Job completed', [
        'job' => $event->job->resolveName()
    ]);
});

Queue::failing(function($event) {
    Log::error('Job failed', [
        'job' => $event->job->resolveName(),
        'exception' => $event->exception->getMessage()
    ]);
});

Queue::exceptionOccurred(function($event) {
    Log::warning('Job exception', [
        'job' => $event->job->resolveName(),
        'exception' => $event->exception->getMessage()
    ]);
});
```

---

## Practical Examples

### Send Email Job

```php
class SendWelcomeEmailJob extends Job
{
    use InteractsWithQueue;
    
    protected $user;
    
    public $tries = 3;
    public $timeout = 30;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function handle()
    {
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
    }
    
    public function failed(\Exception $exception)
    {
        Log::error('Welcome email failed', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage()
        ]);
    }
}

// Dispatch
SendWelcomeEmailJob::dispatch($user)->delay(now()->addMinutes(5));
```

### Process Image Job

```php
class ProcessImageJob extends Job
{
    protected $image;
    
    public $tries = 2;
    public $timeout = 120;
    
    public function __construct($image)
    {
        $this->image = $image;
    }
    
    public function handle(ImageProcessor $processor)
    {
        $processor->resize($this->image, 800, 600);
        $processor->optimize($this->image);
        $processor->generateThumbnail($this->image);
        
        $this->image->update(['processed' => true]);
    }
}

// Dispatch to specific queue
ProcessImageJob::dispatch($image)->onQueue('images');
```

### Export Data Job

```php
class ExportUsersJob extends Job
{
    use InteractsWithQueue;
    
    protected $filters;
    protected $userId;
    
    public $tries = 1;
    public $timeout = 600; // 10 minutes
    
    public function __construct($filters, $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }
    
    public function handle()
    {
        $query = User::query();
        
        foreach ($this->filters as $key => $value) {
            $query->where($key, $value);
        }
        
        $filename = 'users-export-' . date('Y-m-d-His') . '.csv';
        $path = storage_path("exports/{$filename}");
        
        $file = fopen($path, 'w');
        fputcsv($file, ['ID', 'Name', 'Email', 'Created At']);
        
        $query->chunk(1000, function($users) use ($file) {
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at
                ]);
            }
        });
        
        fclose($file);
        
        // Notify user
        $user = User::find($this->userId);
        Mail::to($user->email)->send(new ExportReadyEmail($filename));
    }
}
```

### Batch Processing

```php
class ProcessImportJob extends Job
{
    protected $filePath;
    
    public function handle()
    {
        $data = $this->parseFile($this->filePath);
        
        $jobs = collect($data)->map(function($row) {
            return new ImportUserJob($row);
        })->all();
        
        $batch = Batch::make($jobs)
            ->name('Import Users')
            ->allowFailures()
            ->then(function(Batch $batch) {
                Log::info('Import completed', [
                    'total' => $batch->totalJobs,
                    'failed' => $batch->failedJobs
                ]);
            })
            ->dispatch();
    }
}
```

### Chained Jobs

```php
class ProcessOrderChain
{
    public static function create($order)
    {
        Chain::create([
            new ValidateOrderJob($order),
            new ChargePaymentJob($order),
            new UpdateInventoryJob($order),
            new SendInvoiceJob($order),
            new NotifyWarehouseJob($order)
        ])
        ->then(function() use ($order) {
            $order->update(['status' => 'completed']);
            Log::info('Order processed successfully', ['order_id' => $order->id]);
        })
        ->catch(function(\Exception $e) use ($order) {
            $order->update(['status' => 'failed']);
            Log::error('Order processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        })
        ->dispatch();
    }
}
```

### Scheduled Job

```php
// In App\Console\Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new GenerateDailyReportJob())
        ->daily()
        ->at('02:00');
    
    $schedule->job(new CleanupTempFilesJob())
        ->hourly();
    
    $schedule->job(new SendWeeklyNewsletterJob())
        ->weekly()
        ->sundays()
        ->at('08:00');
}
```

---

## Queue Workers

### Starting Workers

```bash
# Start worker
php neo queue:work

# Specific connection
php neo queue:work redis

# Specific queue
php neo queue:work --queue=emails

# Multiple queues with priority
php neo queue:work --queue=high,default,low

# Process one job and stop
php neo queue:work --once

# Stop after processing all jobs
php neo queue:work --stop-when-empty
```

### Worker Options

```bash
# Max jobs before restart
php neo queue:work --max-jobs=1000

# Max execution time
php neo queue:work --max-time=3600

# Memory limit
php neo queue:work --memory=512

# Job timeout
php neo queue:work --timeout=60

# Sleep duration when no jobs
php neo queue:work --sleep=3

# Number of seconds to wait before retrying
php neo queue:work --backoff=3

# Max attempts
php neo queue:work --tries=3
```

---

## Next Steps

- [Events Classes](events.md)
- [Mail Classes](mail.md)
- [Storage Classes](storage.md)
- [Testing Guide](../tutorials/testing-guide.md)
