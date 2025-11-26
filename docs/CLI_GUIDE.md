# CLI Reference

Command-line tools for NeoPhp.

## Overview

The `neo` command provides code generation and database management tools.

```bash
php neo <command> [options] [arguments]
```

## Available Commands

```bash
php neo list              # Show all commands
php neo --help           # Show help
php neo --version        # Show version
```

## Code Generation

### Controllers

```bash
php neo make:controller UserController
```

Creates `app/Controllers/UserController.php` with CRUD methods:

```php
<?php namespace App\Controllers;

use NeoPhp\Http\Controller;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class UserController extends Controller
{
    public function index(Request $request): Response {}
    public function show(Request $request, int $id): Response {}
    public function store(Request $request): Response {}
    public function update(Request $request, int $id): Response {}
    public function destroy(Request $request, int $id): Response {}
}
```

### Models

```bash
php neo make:model User
```

Creates `app/Models/User.php` with metadata attributes:

```php
<?php namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Metadata\{Table, Field};

#[Table('users')]
class User extends Model
{
    #[Field('id', type: 'integer', primary: true, autoIncrement: true)]
    public int $id;
    
    #[Field('name', type: 'string', length: 255)]
    public string $name;
    
    #[Field('email', type: 'string', length: 255)]
    public string $email;
}
```

Create model with migration:

```bash
php neo make:model User -m
```

This creates both the model and a migration file.
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
### Migrations

```bash
php neo make:migration create_users_table
```

Creates a timestamped migration file in `database/migrations/`.

Use the schema builder in your migration:

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
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

Available column types:

```php
$table->id();                          // Auto-increment ID
$table->string('name', 255);           // VARCHAR
$table->text('description');           // TEXT
$table->integer('count');              // INT
$table->decimal('price', 8, 2);        // DECIMAL
$table->boolean('is_active');          // TINYINT(1)
$table->date('birth_date');            // DATE
$table->datetime('published_at');      // DATETIME
$table->timestamp('created_at');       // TIMESTAMP
$table->timestamps();                  // created_at + updated_at
$table->softDeletes();                 // deleted_at
$table->json('metadata');              // JSON

// Indexes
$table->index('email');
$table->unique('username');

// Foreign keys
$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('cascade');
```

### Middleware

```bash
php neo make:middleware AuthMiddleware
```

Creates `app/Middleware/AuthMiddleware.php`:

```php
<?php namespace App\Middleware;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;
use NeoPhp\Http\Middleware;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Pre-processing
        
        $response = $next($request);
        
        // Post-processing
        
        return $response;
    }
}
```

### Service Providers

```bash
php neo make:provider PaymentServiceProvider
```

Creates `app/Providers/PaymentServiceProvider.php`:

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
        // Bootstrap code
    }

    public function provides(): array
    {
        return ['payment'];
    }
}
```

### Plugins

```bash
php neo make:plugin Blog
```

You'll be prompted for description and author. Creates `plugins/blog/BlogPlugin.php`:

```php
<?php namespace Plugins\Blog;

use NeoPhp\Plugin\Plugin;

class BlogPlugin extends Plugin
{
    protected string $name = 'blog';
    protected string $version = '1.0.0';
    protected string $description = 'Blog plugin';
    protected string $author = 'Your Name';

    public function install(): void
    {
        // Create tables, copy files
    }

    public function uninstall(): void
    {
        // Cleanup
    }

    public function boot(): void
    {
        // Register routes, hooks, services
    }
}
```

### Custom Commands

```bash
php neo make:command ProcessDataCommand
```

You'll be prompted for the command signature and description. Creates `app/Console/Commands/ProcessDataCommand.php`:

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
        
        // Your logic
        
        $this->success('Complete!');
        return 0;
    }
}
```

## Migrations

### Run Migrations

```bash
php neo migrate
```

Runs all pending migrations.

### Rollback

```bash
php neo migrate:rollback           # Rollback last batch
php neo migrate:rollback --step=2  # Rollback 2 batches
```

### Reset

```bash
php neo migrate:reset    # Rollback all migrations
```

### Refresh

```bash
php neo migrate:refresh  # Reset and re-run all
```

### Fresh

```bash
php neo migrate:fresh    # Drop all tables and re-run
```

### Status

```bash
php neo migrate:status
```

Shows which migrations have been run:

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

## Database

### Seed Database

```bash
php neo db:seed                    # Run DatabaseSeeder
php neo db:seed --class=UserSeeder # Run specific seeder
```

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
```bash
php neo db:wipe  # Drop all tables
```

## Plugins

### List Plugins

```bash
php neo plugin:list
```

Shows installed plugins:

```
Installed Plugins:

┌──────┬─────────┬────────┬──────────────────┐
│ Name │ Version │ Status │ Description      │
├──────┼─────────┼────────┼──────────────────┤
│ blog │ 1.0.0   │ Active │ Blog plugin      │
│ shop │ 2.1.0   │ Active │ E-commerce plugin│
└──────┴─────────┴────────┴──────────────────┘
```

## Other Commands

### Clear Cache

```bash
php neo cache:clear
```

### Development Server

```bash
php neo serve                        # Start on localhost:8000
php neo serve --host=0.0.0.0 --port=9000  # Custom host/port
```

## Creating Custom Commands

Generate a command:

```bash
php neo make:command SendEmailCommand
```

Edit the generated file:

```php
<?php namespace App\Console\Commands;

use NeoPhp\Console\Command;

class SendEmailCommand extends Command
{
    protected string $signature = 'email:send {recipient} {--subject=} {--body=}';
    protected string $description = 'Send an email';

    public function handle(): int
    {
        $recipient = $this->argument(0);
        $subject = $this->option('subject') ?? 'No Subject';
        
        $this->info("Sending to {$recipient}...");
        
        // Send email
        
        $this->success('Sent!');
        return 0;
    }
}
```

### Command Methods

**Arguments & Options:**

```php
$this->argument(0);        // Get argument by index
$this->option('flag');     // Get option value
$this->hasOption('flag');  // Check if option exists
```

**Output:**

```php
$this->info('Info');
$this->success('Success');
$this->error('Error');
$this->warning('Warning');
$this->line('Text');
```

**Input:**

```php
$name = $this->ask('What is your name?', 'Default');
$confirmed = $this->confirm('Are you sure?', false);
$password = $this->secret('Enter password');
$choice = $this->choice('Select', ['a', 'b', 'c'], 0);
```

**Tables:**

```php
$this->table(
    ['ID', 'Name'],
    [[1, 'John'], [2, 'Jane']]
);
```

**Progress:**

```php
$this->progressStart(100);
for ($i = 0; $i < 100; $i++) {
    $this->progressAdvance();
}
$this->progressFinish();
```

**Call Commands:**

```php
$this->call('cache:clear');
$this->call('migrate', ['--force' => true]);
```

## Command Reference

### Code Generation

| Command | Description |
|---------|-------------|
| `make:controller` | Create controller |
| `make:model` | Create model |
| `make:migration` | Create migration |
| `make:middleware` | Create middleware |
| `make:provider` | Create service provider |
| `make:plugin` | Create plugin |
| `make:command` | Create console command |

### Migrations

| Command | Description |
|---------|-------------|
| `migrate` | Run migrations |
| `migrate:rollback` | Rollback last batch |
| `migrate:reset` | Rollback all |
| `migrate:refresh` | Reset and re-run |
| `migrate:fresh` | Drop all and re-run |
| `migrate:status` | Show status |

### Database

| Command | Description |
|---------|-------------|
| `db:seed` | Seed database |
| `db:wipe` | Drop all tables |

### Plugins

| Command | Description |
|---------|-------------|
| `plugin:install` | Install plugin |
| `plugin:uninstall` | Uninstall plugin |
| `plugin:list` | List plugins |

### Other

| Command | Description |
|---------|-------------|
| `cache:clear` | Clear cache |
| `serve` | Start dev server |

## Tips

- Always use migrations for schema changes
- Name migrations descriptively (e.g., `create_users_table`, `add_status_to_posts`)
- Use seeders for test data
- Create custom commands for repetitive tasks

## Troubleshooting

### Command not found

```bash
# Windows
php neo list

# Linux/Mac
chmod +x neo
./neo list
```

### Migration errors

```bash
php neo migrate:status  # Check status
php neo migrate:reset   # Reset if needed
```

### Autoload issues

```bash
composer dump-autoload
```

For more details, see the [Foundation Guide](FOUNDATION_GUIDE.md).

**NeoPhp CLI** - Build faster, deploy smarter! 🚀
