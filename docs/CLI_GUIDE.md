# NeoPhp CLI Guide

Complete guide for using the NeoPhp Command Line Interface (CLI).

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Basic Usage](#basic-usage)
4. [Make Commands](#make-commands)
5. [Migration Commands](#migration-commands)
6. [Database Commands](#database-commands)
7. [Plugin Commands](#plugin-commands)
8. [Other Commands](#other-commands)
9. [Creating Custom Commands](#creating-custom-commands)

---

## Introduction

NeoPhp provides a powerful command-line interface called `neo` (inspired by Laravel's artisan but with its own identity). The CLI allows you to:

- Generate code (controllers, models, migrations, etc.)
- Run database migrations
- Manage plugins
- Clear cache
- Start development server
- Create custom commands

**Command Pattern:**
```bash
php neo <command> [options] [arguments]
```

---

## Installation

The `neo` CLI is automatically available after installing NeoPhp. Make sure the `neo` file in the root directory is executable:

**Windows:**
```bash
# No special permissions needed
php neo list
```

**Linux/Mac:**
```bash
chmod +x neo
./neo list
# Or with php
php neo list
```

---

## Basic Usage

### List All Commands
```bash
php neo list
php neo
```

### Get Help
```bash
php neo --help
php neo -h
```

### Get Version
```bash
php neo --version
php neo -V
```

### Command Help
```bash
php neo <command> --help
```

---

## Make Commands

Generate new files from templates.

### Make Controller

```bash
php neo make:controller UserController
```

Creates: `app/Controllers/UserController.php`

**Features:**
- Automatically appends "Controller" suffix
- Includes CRUD methods (index, show, store, update, destroy)
- Ready-to-use JSON responses

**Example Output:**
```php
<?php namespace App\Controllers;

use NeoPhp\Http\Controller;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class UserController extends Controller
{
    public function index(Request $request): Response { }
    public function show(Request $request, int $id): Response { }
    public function store(Request $request): Response { }
    public function update(Request $request, int $id): Response { }
    public function destroy(Request $request, int $id): Response { }
}
```

---

### Make Model

```bash
php neo make:model User
```

Creates: `app/Models/User.php`

**With Migration:**
```bash
php neo make:model User --migration
php neo make:model User -m
```

Creates:
- `app/Models/User.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_users_table.php`

**Features:**
- Auto-generates table name (User → users)
- Includes metadata attributes (#[Table], #[Field])
- Timestamps included
- Fillable fields array

**Example Output:**
```php
<?php namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Metadata\Table;
use NeoPhp\Metadata\Field;

#[Table(name: 'users')]
class User extends Model
{
    #[Field(type: 'int', primaryKey: true, autoIncrement: true)]
    public int $id;

    #[Field(type: 'varchar', length: 255, nullable: false, required: true)]
    public string $name;

    #[Field(type: 'timestamp', nullable: true, default: 'CURRENT_TIMESTAMP')]
    public ?string $created_at;

    #[Field(type: 'timestamp', nullable: true, onUpdate: 'CURRENT_TIMESTAMP')]
    public ?string $updated_at;
}
```

---

### Make Migration

```bash
php neo make:migration create_users_table
```

Creates: `database/migrations/2024_11_26_123456_create_users_table.php`

**Naming Conventions:**
- `create_<table>_table` - Create new table
- `add_<column>_to_<table>_table` - Add column
- `remove_<column>_from_<table>_table` - Remove column

**Example Output:**
```php
<?php

use NeoPhp\Database\Migrations\Migration;
use NeoPhp\Database\Schema\Schema;
use NeoPhp\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

**Available Schema Builder Methods:**
```php
$table->id();                          // Auto-increment primary key
$table->string('name', 255);           // VARCHAR
$table->text('description');           // TEXT
$table->integer('count');              // INT
$table->bigInteger('big_number');      // BIGINT
$table->decimal('price', 8, 2);        // DECIMAL
$table->boolean('is_active');          // TINYINT(1)
$table->date('birth_date');            // DATE
$table->datetime('published_at');      // DATETIME
$table->timestamp('created_at');       // TIMESTAMP
$table->timestamps();                  // created_at + updated_at
$table->softDeletes();                 // deleted_at
$table->json('metadata');              // JSON
$table->uuid('uuid');                  // VARCHAR(36)

// Indexes
$table->index('email');
$table->unique('username');
$table->primary(['id', 'type']);

// Foreign Keys
$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('CASCADE');
```

---

### Make Middleware

```bash
php neo make:middleware AuthMiddleware
```

Creates: `app/Middleware/AuthMiddleware.php`

**Example Output:**
```php
<?php namespace App\Middleware;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;
use NeoPhp\Http\Middleware;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Process request before controller
        
        $response = $next($request);
        
        // Process response after controller
        
        return $response;
    }
}
```

---

### Make Service Provider

```bash
php neo make:provider PaymentServiceProvider
```

Creates: `app/Providers/PaymentServiceProvider.php`

**Features:**
- Automatically appends "ServiceProvider" suffix
- Includes register() and boot() methods
- Deferred loading support

**Example Output:**
```php
<?php namespace App\Providers;

use NeoPhp\Foundation\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('payment', function ($app) {
            return new \App\Services\Payment();
        });
    }

    public function boot(): void
    {
        // Bootstrap services
    }

    public function provides(): array
    {
        return ['payment'];
    }
}
```

---

### Make Plugin

```bash
php neo make:plugin Blog
```

**Interactive Prompts:**
- Plugin description
- Plugin author

Creates: `plugins/blog/BlogPlugin.php`

**Example Output:**
```php
<?php namespace Plugins\Blog;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Plugin\HookManager;

class BlogPlugin extends Plugin
{
    protected string $name = 'blog';
    protected string $version = '1.0.0';
    protected string $description = 'A blog plugin';
    protected string $author = 'Your Name';

    public function install(): void { }
    public function uninstall(): void { }
    public function boot(): void { }
}
```

---

### Make Command

```bash
php neo make:command ProcessDataCommand
```

**Interactive Prompts:**
- Command signature (e.g., app:process)
- Command description

Creates: `app/Console/Commands/ProcessDataCommand.php`

**Example Output:**
```php
<?php namespace App\Console\Commands;

use NeoPhp\Console\Command;

class ProcessDataCommand extends Command
{
    protected string $signature = 'app:process';
    protected string $description = 'Process application data';

    public function handle(): int
    {
        $this->info('Processing...');
        
        // Your logic here
        
        $this->success('Complete!');
        return 0;
    }
}
```

---

## Migration Commands

Manage database schema changes.

### Run Migrations

```bash
php neo migrate
```

Runs all pending migrations in `database/migrations/`.

**Output:**
```
Running migrations...

✓ Migrated: 2024_11_26_123456_create_users_table
✓ Migrated: 2024_11_26_123457_create_posts_table

✓ Migration completed successfully!
```

---

### Rollback Last Migration

```bash
php neo migrate:rollback
```

Rollback the last batch of migrations.

**Rollback Multiple Batches:**
```bash
php neo migrate:rollback --step=2
```

---

### Reset All Migrations

```bash
php neo migrate:reset
```

Rollback ALL migrations (interactive confirmation required).

---

### Refresh Migrations

```bash
php neo migrate:refresh
```

Equivalent to:
1. `migrate:reset`
2. `migrate`

---

### Fresh Migration

```bash
php neo migrate:fresh
```

Drop all tables and re-run migrations (interactive confirmation required).

---

### Migration Status

```bash
php neo migrate:status
```

**Output:**
```
Migration Status:

┌─────┬────────────────────────────────────────┬───────┐
│ Ran?│ Migration                              │ Batch │
├─────┼────────────────────────────────────────┼───────┤
│ Yes │ 2024_11_26_123456_create_users_table   │ 1     │
│ Yes │ 2024_11_26_123457_create_posts_table   │ 1     │
│ No  │ 2024_11_26_123458_create_comments_table│ -     │
└─────┴────────────────────────────────────────┴───────┘
```

---

## Database Commands

### Seed Database

```bash
php neo db:seed
```

Runs `DatabaseSeeder` class.

**Specific Seeder:**
```bash
php neo db:seed --class=UserSeeder
```

---

### Wipe Database

```bash
php neo db:wipe
```

Drop all tables (interactive confirmation required).

---

## Plugin Commands

### Install Plugin

```bash
php neo plugin:install blog
```

---

### Uninstall Plugin

```bash
php neo plugin:uninstall blog
```

---

### List Plugins

```bash
php neo plugin:list
```

**Output:**
```
Installed Plugins:

┌──────┬─────────┬────────┬──────────────────┐
│ Name │ Version │ Status │ Description      │
├──────┼─────────┼────────┼──────────────────┤
│ blog │ 1.0.0   │ Active │ Blog plugin      │
│ shop │ 2.1.0   │ Active │ E-commerce plugin│
└──────┴─────────┴────────┴──────────────────┘
```

---

## Other Commands

### Clear Cache

```bash
php neo cache:clear
```

---

### Start Development Server

```bash
php neo serve
```

Starts server at `http://localhost:8000`

**Custom Host/Port:**
```bash
php neo serve --host=0.0.0.0 --port=9000
```

---

## Creating Custom Commands

### 1. Generate Command

```bash
php neo make:command SendEmailCommand
```

### 2. Edit Command

```php
<?php namespace App\Console\Commands;

use NeoPhp\Console\Command;

class SendEmailCommand extends Command
{
    protected string $signature = 'email:send {recipient} {--subject=} {--body=}';
    protected string $description = 'Send an email to a recipient';

    public function handle(): int
    {
        $recipient = $this->argument(0);
        $subject = $this->option('subject') ?? 'No Subject';
        $body = $this->option('body') ?? '';

        $this->info("Sending email to {$recipient}...");
        
        // Send email logic
        
        $this->success('Email sent!');
        
        return 0;
    }
}
```

### 3. Available Methods

**Arguments:**
```php
$this->argument(0);        // Get argument by index
$this->argument('name');   // Get named argument
```

**Options:**
```php
$this->option('flag');     // Get option value
$this->hasOption('flag');  // Check if option exists
```

**Output:**
```php
$this->info('Information message');
$this->success('Success message');
$this->error('Error message');
$this->warning('Warning message');
$this->comment('Comment message');
$this->line('Plain text');
$this->newLine(2);
```

**Input:**
```php
$name = $this->ask('What is your name?', 'Default');
$confirmed = $this->confirm('Are you sure?', false);
$password = $this->secret('Enter password');
$choice = $this->choice('Select option', ['a', 'b', 'c'], 0);
```

**Tables:**
```php
$this->table(
    ['ID', 'Name', 'Email'],
    [
        [1, 'John', 'john@example.com'],
        [2, 'Jane', 'jane@example.com'],
    ]
);
```

**Progress Bar:**
```php
$this->progressStart(100);
for ($i = 0; $i < 100; $i++) {
    // Do work
    $this->progressAdvance();
}
$this->progressFinish();
```

**Call Other Commands:**
```php
$this->call('cache:clear');
$this->call('migrate', ['--force' => true]);
```

---

## Command Reference

### Make Commands
| Command | Description |
|---------|-------------|
| `make:controller` | Create a new controller |
| `make:model` | Create a new model |
| `make:migration` | Create a new migration |
| `make:middleware` | Create a new middleware |
| `make:provider` | Create a new service provider |
| `make:plugin` | Create a new plugin |
| `make:command` | Create a new console command |

### Migration Commands
| Command | Description |
|---------|-------------|
| `migrate` | Run pending migrations |
| `migrate:rollback` | Rollback last migration batch |
| `migrate:reset` | Rollback all migrations |
| `migrate:refresh` | Reset and re-run migrations |
| `migrate:fresh` | Drop all tables and re-run |
| `migrate:status` | Show migration status |

### Database Commands
| Command | Description |
|---------|-------------|
| `db:seed` | Seed the database |
| `db:wipe` | Drop all tables |

### Plugin Commands
| Command | Description |
|---------|-------------|
| `plugin:install` | Install a plugin |
| `plugin:uninstall` | Uninstall a plugin |
| `plugin:list` | List all plugins |

### Other Commands
| Command | Description |
|---------|-------------|
| `cache:clear` | Clear application cache |
| `serve` | Start development server |
| `list` | List all commands |
| `--version` | Show version |
| `--help` | Show help |

---

## Tips & Best Practices

1. **Use Migrations for Schema Changes**: Never modify database directly
2. **Name Migrations Clearly**: Follow Laravel naming conventions
3. **Create Seeders for Test Data**: Use `db:seed` for development
4. **Use Make Commands**: Don't create files manually
5. **Custom Commands**: Create commands for repetitive tasks
6. **Development Server**: Use `php neo serve` instead of configuring Apache/Nginx

---

## Troubleshooting

### Command Not Found
```bash
# Windows
php neo list

# Linux/Mac - Make sure neo is executable
chmod +x neo
./neo list
```

### Migration Errors
```bash
# Check migration status
php neo migrate:status

# Reset and try again
php neo migrate:reset
php neo migrate
```

### Autoload Issues
```bash
# Regenerate autoload
composer dump-autoload
```

---

## Next Steps

- Read [Foundation Guide](FOUNDATION_GUIDE.md) for core concepts
- Check [examples/](examples/) for working examples
- Create your first controller: `php neo make:controller HomeController`
- Create your first model: `php neo make:model Product -m`

---

**NeoPhp CLI** - Build faster, deploy smarter! 🚀
