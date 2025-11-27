# Custom Commands

Create your own CLI commands to automate tasks specific to your application.

## Creating Commands

### Generate Command

```bash
# Create a command
php neo make:command SendEmails

# Create with signature
php neo make:command SendEmails --signature="email:send"

# Create scheduled command
php neo make:command CleanupLogs --schedule
```

Generated command:

```php
<?php

namespace App\Commands;

use NeoPhp\Console\Command;

class SendEmails extends Command
{
    /**
     * Command signature
     */
    protected string $signature = 'email:send {user} {--queue}';
    
    /**
     * Command description
     */
    protected string $description = 'Send emails to users';
    
    /**
     * Execute the command
     */
    public function handle(): int
    {
        $user = $this->argument('user');
        $queue = $this->option('queue');
        
        $this->info("Sending emails to: {$user}");
        
        if ($queue) {
            $this->line('Using queue...');
        }
        
        // Your command logic here
        
        $this->success('Emails sent successfully!');
        
        return self::SUCCESS;
    }
}
```

## Command Structure

### Basic Command

```php
<?php

namespace App\Commands;

use NeoPhp\Console\Command;

class MyCommand extends Command
{
    protected string $signature = 'my:command';
    protected string $description = 'My custom command';
    
    public function handle(): int
    {
        $this->info('Command executed!');
        return self::SUCCESS;
    }
}
```

### Command with Arguments

```php
protected string $signature = 'user:create {name} {email}';

public function handle(): int
{
    $name = $this->argument('name');
    $email = $this->argument('email');
    
    $this->info("Creating user: {$name} ({$email})");
    
    return self::SUCCESS;
}
```

### Command with Options

```php
protected string $signature = 'user:create {name} {--admin} {--role=}';

public function handle(): int
{
    $name = $this->argument('name');
    $isAdmin = $this->option('admin');
    $role = $this->option('role');
    
    $this->info("Name: {$name}");
    $this->info("Admin: " . ($isAdmin ? 'yes' : 'no'));
    $this->info("Role: " . ($role ?? 'default'));
    
    return self::SUCCESS;
}
```

## Command Signature Syntax

### Arguments

```php
// Required argument
'command {user}'

// Optional argument
'command {user?}'

// Argument with default value
'command {user=guest}'

// Argument with description
'command {user : The user name}'

// Array argument
'command {users*}'
```

### Options

```php
// Boolean option (--flag)
'command {--flag}'

// Option with value (--name=value)
'command {--name=}'

// Option with default value
'command {--name=default}'

// Option with shortcut (-n, --name)
'command {--n|name=}'

// Array option
'command {--ids=*}'
```

### Complete Example

```php
protected string $signature = 'user:create 
    {name : The user name}
    {email : The user email}
    {--admin : Make user admin}
    {--role=user : User role}
    {--notify : Send notification}';
```

## Input/Output

### Get Input

```php
public function handle(): int
{
    // Get argument
    $name = $this->argument('name');
    
    // Get all arguments
    $arguments = $this->arguments();
    
    // Get option
    $admin = $this->option('admin');
    
    // Get all options
    $options = $this->options();
    
    return self::SUCCESS;
}
```

### Prompting for Input

```php
public function handle(): int
{
    // Ask question
    $name = $this->ask('What is your name?');
    
    // Ask with default
    $name = $this->ask('What is your name?', 'Guest');
    
    // Secret input (password)
    $password = $this->secret('Enter password:');
    
    // Confirm (yes/no)
    if ($this->confirm('Do you want to continue?')) {
        // User said yes
    }
    
    // Choice
    $role = $this->choice(
        'Select role:',
        ['admin', 'editor', 'user'],
        0  // default index
    );
    
    return self::SUCCESS;
}
```

### Output Messages

```php
public function handle(): int
{
    // Regular output
    $this->line('Regular message');
    
    // Colored output
    $this->info('Information message');      // Green
    $this->success('Success message');       // Green with ✓
    $this->comment('Comment message');       // Yellow
    $this->question('Question message');     // Cyan
    $this->error('Error message');          // Red
    $this->warn('Warning message');         // Yellow
    
    // Custom colors
    $this->line('<fg=blue>Blue text</>');
    $this->line('<bg=yellow>Yellow background</>');
    $this->line('<options=bold>Bold text</>');
    
    return self::SUCCESS;
}
```

### Tables

```php
public function handle(): int
{
    $headers = ['Name', 'Email', 'Role'];
    
    $users = [
        ['John Doe', 'john@example.com', 'Admin'],
        ['Jane Smith', 'jane@example.com', 'Editor'],
        ['Bob Johnson', 'bob@example.com', 'User'],
    ];
    
    $this->table($headers, $users);
    
    return self::SUCCESS;
}
```

### Progress Bar

```php
public function handle(): int
{
    $users = User::all();
    
    $this->info('Processing users...');
    
    $bar = $this->createProgressBar(count($users));
    
    foreach ($users as $user) {
        // Process user
        $this->processUser($user);
        
        $bar->advance();
    }
    
    $bar->finish();
    
    $this->newLine();
    $this->success('All users processed!');
    
    return self::SUCCESS;
}
```

### Spinner

```php
public function handle(): int
{
    $this->withSpinner(
        fn() => $this->longRunningTask(),
        'Processing...'
    );
    
    return self::SUCCESS;
}
```

## Real-World Examples

### Send Email Command

```php
<?php

namespace App\Commands;

use NeoPhp\Console\Command;
use App\Models\User;
use App\Services\EmailService;

class SendEmailCommand extends Command
{
    protected string $signature = 'email:send 
        {user? : User ID or email}
        {--all : Send to all users}
        {--subject= : Email subject}
        {--template= : Email template}
        {--queue : Use queue}';
    
    protected string $description = 'Send emails to users';
    
    public function __construct(
        private EmailService $emailService
    ) {
        parent::__construct();
    }
    
    public function handle(): int
    {
        $subject = $this->option('subject') ?? 'Newsletter';
        $template = $this->option('template') ?? 'default';
        $useQueue = $this->option('queue');
        
        if ($this->option('all')) {
            $users = User::all();
        } else {
            $userId = $this->argument('user');
            if (!$userId) {
                $this->error('Please provide user ID or use --all option');
                return self::FAILURE;
            }
            $users = User::where('id', $userId)
                        ->orWhere('email', $userId)
                        ->get();
        }
        
        if ($users->isEmpty()) {
            $this->warn('No users found');
            return self::FAILURE;
        }
        
        $this->info("Sending emails to {$users->count()} users");
        
        if (!$this->confirm('Continue?', true)) {
            $this->comment('Cancelled');
            return self::FAILURE;
        }
        
        $bar = $this->createProgressBar($users->count());
        
        foreach ($users as $user) {
            if ($useQueue) {
                $this->emailService->queueEmail($user, $subject, $template);
            } else {
                $this->emailService->sendEmail($user, $subject, $template);
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->success('Emails sent successfully!');
        
        return self::SUCCESS;
    }
}
```

### Database Cleanup Command

```php
<?php

namespace App\Commands;

use NeoPhp\Console\Command;
use App\Models\Log;
use App\Models\TempFile;

class CleanupCommand extends Command
{
    protected string $signature = 'cleanup:run 
        {--days=30 : Days to keep}
        {--dry-run : Show what would be deleted}';
    
    protected string $description = 'Clean up old logs and temporary files';
    
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        
        $this->info("Cleaning up data older than {$days} days");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }
        
        // Clean logs
        $logsCount = Log::where('created_at', '<', now()->subDays($days))->count();
        $this->line("Logs to delete: {$logsCount}");
        
        if (!$dryRun && $logsCount > 0) {
            Log::where('created_at', '<', now()->subDays($days))->delete();
            $this->success("Deleted {$logsCount} old logs");
        }
        
        // Clean temp files
        $filesCount = TempFile::where('created_at', '<', now()->subDays($days))->count();
        $this->line("Temp files to delete: {$filesCount}");
        
        if (!$dryRun && $filesCount > 0) {
            TempFile::where('created_at', '<', now()->subDays($days))->delete();
            $this->success("Deleted {$filesCount} temp files");
        }
        
        $totalDeleted = $logsCount + $filesCount;
        $this->newLine();
        $this->success("Cleanup complete! Total items: {$totalDeleted}");
        
        return self::SUCCESS;
    }
}
```

### Import Data Command

```php
<?php

namespace App\Commands;

use NeoPhp\Console\Command;
use App\Models\Product;

class ImportProductsCommand extends Command
{
    protected string $signature = 'import:products 
        {file : CSV file path}
        {--batch=100 : Batch size}';
    
    protected string $description = 'Import products from CSV file';
    
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $batchSize = (int) $this->option('batch');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }
        
        $this->info("Importing products from: {$filePath}");
        
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        $rows = [];
        $total = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $row);
            $total++;
            
            if (count($rows) >= $batchSize) {
                $this->importBatch($rows);
                $rows = [];
            }
        }
        
        if (!empty($rows)) {
            $this->importBatch($rows);
        }
        
        fclose($handle);
        
        $this->success("Imported {$total} products");
        
        return self::SUCCESS;
    }
    
    private function importBatch(array $rows): void
    {
        foreach ($rows as $row) {
            Product::create([
                'name' => $row['name'],
                'sku' => $row['sku'],
                'price' => $row['price'],
                'description' => $row['description'] ?? null,
            ]);
        }
    }
}
```

## Scheduling Commands

### Schedule Definition

In `app/Console/Kernel.php`:

```php
<?php

namespace App\Console;

use NeoPhp\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(): void
    {
        // Run daily at midnight
        $this->command('cleanup:run')
             ->daily();
        
        // Run every hour
        $this->command('email:send --all')
             ->hourly();
        
        // Run every 5 minutes
        $this->command('sync:data')
             ->everyFiveMinutes();
        
        // Run on weekdays at 2pm
        $this->command('report:generate')
             ->weekdays()
             ->at('14:00');
        
        // Run with timezone
        $this->command('backup:database')
             ->daily()
             ->at('01:00')
             ->timezone('America/New_York');
    }
}
```

### Schedule Frequencies

```php
->everyMinute()              // Every minute
->everyFiveMinutes()         // Every 5 minutes
->everyTenMinutes()          // Every 10 minutes
->everyFifteenMinutes()      // Every 15 minutes
->everyThirtyMinutes()       // Every 30 minutes
->hourly()                   // Every hour
->hourlyAt(17)               // Every hour at :17
->daily()                    // Daily at midnight
->dailyAt('13:00')           // Daily at 1pm
->twiceDaily(1, 13)          // Daily at 1am and 1pm
->weekly()                   // Weekly on Sunday at midnight
->weeklyOn(1, '08:00')       // Weekly on Monday at 8am
->monthly()                  // Monthly on the 1st at midnight
->monthlyOn(4, '15:00')      // Monthly on 4th at 3pm
->quarterly()                // Quarterly
->yearly()                   // Yearly
```

## Testing Commands

```php
<?php

namespace Tests\Commands;

use Tests\TestCase;
use App\Commands\SendEmailCommand;

class SendEmailCommandTest extends TestCase
{
    public function test_sends_email_to_user()
    {
        $this->artisan('email:send', ['user' => 1])
             ->expectsOutput('Emails sent successfully!')
             ->assertExitCode(0);
    }
    
    public function test_requires_confirmation()
    {
        $this->artisan('email:send', ['--all' => true])
             ->expectsConfirmation('Continue?', 'no')
             ->assertExitCode(1);
    }
}
```

## Registering Commands

In `app/Console/Kernel.php`:

```php
protected array $commands = [
    SendEmailCommand::class,
    CleanupCommand::class,
    ImportProductsCommand::class,
];
```

Or auto-discover from directory:

```php
protected function commands(): void
{
    $this->load(__DIR__ . '/Commands');
}
```

## Best Practices

### 1. Use Descriptive Names

```php
// Good ✅
protected string $signature = 'email:send';
protected string $signature = 'user:create';
protected string $signature = 'cache:clear';

// Bad ❌
protected string $signature = 'send';
protected string $signature = 'create';
```

### 2. Provide Clear Descriptions

```php
// Good ✅
protected string $description = 'Send newsletter emails to all users';

// Bad ❌
protected string $description = 'Send emails';
```

### 3. Return Proper Exit Codes

```php
public function handle(): int
{
    if ($error) {
        return self::FAILURE;  // 1
    }
    
    return self::SUCCESS;  // 0
}
```

### 4. Handle Errors Gracefully

```php
try {
    $this->processData();
    $this->success('Complete!');
    return self::SUCCESS;
} catch (\Exception $e) {
    $this->error("Error: {$e->getMessage()}");
    return self::FAILURE;
}
```

### 5. Use Dependency Injection

```php
public function __construct(
    private EmailService $emailService,
    private LoggerInterface $logger
) {
    parent::__construct();
}
```

## Next Steps

- [CLI Tools Introduction](introduction.md)
- [Database Commands](database-commands.md)
- [Plugin Commands](plugin-commands.md)
- [Task Scheduling](../advanced/scheduling.md)
