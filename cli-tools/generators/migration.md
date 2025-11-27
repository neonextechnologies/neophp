# Migration Generator

The migration generator creates database migration files from your models' metadata, making it easy to version control your database schema.

## Basic Usage

```bash
# Generate migration from model
php neo make:migration --from-model=Product

# Generate empty migration
php neo make:migration create_products_table

# Generate migration with schema
php neo make:migration create_products_table --create=products

# Generate migration to modify table
php neo make:migration add_price_to_products --table=products
```

## Command Syntax

```bash
php neo make:migration [name] [options]
```

### Arguments

- `name` - Migration name (e.g., create_products_table, add_fields_to_users)

### Options

- `--from-model=` - Generate from model metadata
- `--create=` - Table to create
- `--table=` - Table to modify
- `--force` - Overwrite existing file

## Generating from Models

### Basic Migration from Model

```bash
php neo make:migration --from-model=Product
```

If your `Product` model has metadata:

```php
#[Table('products')]
#[Timestamps]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Unique]
    public string $name;
    
    #[Field(type: 'string', length: 100)]
    #[Unique]
    public string $sku;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    public float $price;
    
    #[Field(type: 'text', nullable: true)]
    public ?string $description;
    
    #[Field(type: 'boolean', default: true)]
    public bool $in_stock;
}
```

This generates `database/migrations/2024_01_15_120000_create_products_table.php`:

```php
<?php

use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->string('sku', 100)->unique();
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('products');
    }
};
```

## Manual Migration Creation

### Create Table Migration

```bash
php neo make:migration create_users_table --create=users
```

```php
<?php

use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('users');
    }
};
```

### Modify Table Migration

```bash
php neo make:migration add_avatar_to_users --table=users
```

```php
<?php

use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
```

## Column Types

### String Columns

```php
$table->string('name'); // VARCHAR(255)
$table->string('code', 50); // VARCHAR(50)
$table->text('description'); // TEXT
$table->mediumText('content'); // MEDIUMTEXT
$table->longText('article'); // LONGTEXT
$table->char('code', 10); // CHAR(10)
```

### Numeric Columns

```php
$table->integer('count'); // INT
$table->bigInteger('views'); // BIGINT
$table->tinyInteger('status'); // TINYINT
$table->smallInteger('order'); // SMALLINT
$table->mediumInteger('amount'); // MEDIUMINT

$table->decimal('price', 10, 2); // DECIMAL(10,2)
$table->float('rating'); // FLOAT
$table->double('latitude', 10, 7); // DOUBLE(10,7)

$table->unsignedInteger('count'); // UNSIGNED INT
$table->unsignedBigInteger('user_id'); // UNSIGNED BIGINT
```

### Date and Time Columns

```php
$table->date('birth_date'); // DATE
$table->datetime('published_at'); // DATETIME
$table->time('meeting_time'); // TIME
$table->timestamp('created_at'); // TIMESTAMP
$table->year('year'); // YEAR

// Auto timestamps
$table->timestamps(); // created_at, updated_at
$table->timestampsTz(); // With timezone
$table->softDeletes(); // deleted_at
```

### Boolean and Binary

```php
$table->boolean('is_active'); // TINYINT(1)
$table->binary('data'); // BLOB
```

### JSON and Arrays

```php
$table->json('settings'); // JSON
$table->jsonb('metadata'); // JSONB (PostgreSQL)
```

### Special Columns

```php
$table->id(); // Auto-increment BIGINT UNSIGNED
$table->uuid('id'); // UUID
$table->foreignId('user_id'); // For foreign keys
$table->morphs('commentable'); // commentable_id, commentable_type
$table->rememberToken(); // remember_token VARCHAR(100)
```

## Column Modifiers

```php
$table->string('email')->unique();
$table->string('name')->nullable();
$table->integer('count')->default(0);
$table->string('code')->after('name');
$table->text('description')->comment('Product description');
$table->string('name')->charset('utf8mb4');
$table->string('name')->collation('utf8mb4_unicode_ci');
$table->integer('id')->autoIncrement();
$table->integer('amount')->unsigned();
$table->string('status')->index();
$table->text('content')->fulltext();
```

## Indexes

```php
// Single column index
$table->index('email');
$table->unique('email');
$table->fulltext('description');

// Multiple column index
$table->index(['user_id', 'created_at']);
$table->unique(['email', 'tenant_id']);

// Named index
$table->index('email', 'users_email_index');

// Drop indexes
$table->dropIndex('users_email_index');
$table->dropUnique(['email']);
```

## Foreign Keys

```php
// Basic foreign key
$table->foreignId('user_id')
      ->constrained()
      ->onDelete('cascade');

// Custom table reference
$table->foreignId('author_id')
      ->constrained('users')
      ->onDelete('cascade');

// Full control
$table->unsignedBigInteger('category_id');
$table->foreign('category_id')
      ->references('id')
      ->on('categories')
      ->onDelete('cascade')
      ->onUpdate('cascade');

// Drop foreign key
$table->dropForeign('posts_user_id_foreign');
$table->dropForeign(['user_id']);
```

## Complete Examples

### E-commerce Product Table

```php
public function up(): void
{
    $this->schema->create('products', function (Blueprint $table) {
        // Primary key
        $table->id();
        
        // Basic info
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('sku', 100)->unique();
        
        // Prices
        $table->decimal('price', 10, 2);
        $table->decimal('cost', 10, 2)->nullable();
        $table->decimal('sale_price', 10, 2)->nullable();
        
        // Stock
        $table->integer('stock_quantity')->unsigned()->default(0);
        $table->boolean('in_stock')->default(true);
        $table->boolean('track_inventory')->default(true);
        
        // Content
        $table->text('excerpt')->nullable();
        $table->longText('description')->nullable();
        
        // Media
        $table->string('featured_image')->nullable();
        $table->json('images')->nullable();
        
        // Dimensions
        $table->decimal('weight', 8, 2)->nullable();
        $table->decimal('length', 8, 2)->nullable();
        $table->decimal('width', 8, 2)->nullable();
        $table->decimal('height', 8, 2)->nullable();
        
        // Categories and brands
        $table->foreignId('category_id')
              ->nullable()
              ->constrained()
              ->onDelete('set null');
        
        $table->foreignId('brand_id')
              ->nullable()
              ->constrained()
              ->onDelete('set null');
        
        // Status
        $table->enum('status', ['draft', 'published', 'archived'])
              ->default('draft');
        
        // SEO
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        $table->json('meta_tags')->nullable();
        
        // Timestamps
        $table->timestamps();
        $table->softDeletes();
        
        // Indexes
        $table->index('status');
        $table->index('category_id');
        $table->index('brand_id');
        $table->fulltext(['name', 'description']);
    });
}
```

### Blog Post Table

```php
public function up(): void
{
    $this->schema->create('posts', function (Blueprint $table) {
        $table->id();
        
        // Content
        $table->string('title');
        $table->string('slug')->unique();
        $table->text('excerpt')->nullable();
        $table->longText('content');
        
        // Media
        $table->string('featured_image')->nullable();
        
        // Author
        $table->foreignId('user_id')
              ->constrained()
              ->onDelete('cascade');
        
        // Category
        $table->foreignId('category_id')
              ->nullable()
              ->constrained()
              ->onDelete('set null');
        
        // Status and visibility
        $table->enum('status', ['draft', 'published', 'archived'])
              ->default('draft');
        $table->boolean('featured')->default(false);
        $table->boolean('allow_comments')->default(true);
        
        // Publishing
        $table->timestamp('published_at')->nullable();
        
        // Statistics
        $table->unsignedBigInteger('views')->default(0);
        $table->unsignedInteger('comment_count')->default(0);
        
        // SEO
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        
        // Timestamps
        $table->timestamps();
        $table->softDeletes();
        
        // Indexes
        $table->index(['status', 'published_at']);
        $table->index('user_id');
        $table->index('category_id');
        $table->index('featured');
        $table->fulltext(['title', 'content']);
    });
}
```

### Pivot Table (Many-to-Many)

```php
public function up(): void
{
    $this->schema->create('post_tags', function (Blueprint $table) {
        $table->foreignId('post_id')
              ->constrained()
              ->onDelete('cascade');
        
        $table->foreignId('tag_id')
              ->constrained()
              ->onDelete('cascade');
        
        $table->timestamps();
        
        // Composite primary key
        $table->primary(['post_id', 'tag_id']);
    });
}
```

### Polymorphic Table

```php
public function up(): void
{
    $this->schema->create('comments', function (Blueprint $table) {
        $table->id();
        $table->text('content');
        
        // Polymorphic relationship
        $table->morphs('commentable');
        
        // User who commented
        $table->foreignId('user_id')
              ->constrained()
              ->onDelete('cascade');
        
        $table->timestamps();
        
        // Index on polymorphic columns
        $table->index(['commentable_type', 'commentable_id']);
    });
}
```

## Modifying Tables

### Adding Columns

```php
public function up(): void
{
    $this->schema->table('users', function (Blueprint $table) {
        $table->string('phone')->nullable()->after('email');
        $table->text('bio')->nullable();
        $table->timestamp('last_login_at')->nullable();
    });
}

public function down(): void
{
    $this->schema->table('users', function (Blueprint $table) {
        $table->dropColumn(['phone', 'bio', 'last_login_at']);
    });
}
```

### Modifying Columns

```php
public function up(): void
{
    $this->schema->table('users', function (Blueprint $table) {
        $table->string('email', 320)->change(); // Make email longer
        $table->text('bio')->nullable()->change(); // Make bio nullable
    });
}
```

### Renaming Columns

```php
public function up(): void
{
    $this->schema->table('users', function (Blueprint $table) {
        $table->renameColumn('name', 'full_name');
    });
}

public function down(): void
{
    $this->schema->table('users', function (Blueprint $table) {
        $table->renameColumn('full_name', 'name');
    });
}
```

### Dropping Columns

```php
public function up(): void
{
    $this->schema->table('users', function (Blueprint $table) {
        $table->dropColumn('temp_field');
        
        // Drop multiple columns
        $table->dropColumn(['field1', 'field2', 'field3']);
    });
}
```

## Running Migrations

```bash
# Run all pending migrations
php neo migrate

# Run specific migration
php neo migrate --file=2024_01_15_create_products_table

# Run migrations for specific path
php neo migrate --path=database/migrations/2024

# Rollback last batch
php neo migrate:rollback

# Rollback specific steps
php neo migrate:rollback --step=2

# Reset all migrations
php neo migrate:reset

# Refresh (reset and re-run)
php neo migrate:refresh

# Refresh with seeding
php neo migrate:refresh --seed
```

## Best Practices

### 1. Always Provide Down Methods

```php
// Good ✅
public function down(): void
{
    $this->schema->dropIfExists('products');
}

// Bad ❌
public function down(): void
{
    // Empty
}
```

### 2. Use Foreign Keys

```php
// Good ✅
$table->foreignId('user_id')
      ->constrained()
      ->onDelete('cascade');

// Okay ✓
$table->unsignedBigInteger('user_id');
$table->index('user_id');
```

### 3. Add Indexes to Frequently Queried Columns

```php
$table->index('email');
$table->index('status');
$table->index(['user_id', 'created_at']);
```

### 4. Use Appropriate Column Types

```php
// Good ✅
$table->boolean('is_active');
$table->enum('status', ['draft', 'published']);
$table->decimal('price', 10, 2);

// Bad ❌
$table->string('is_active'); // Should be boolean
$table->string('status'); // Should be enum
$table->float('price'); // Should be decimal for money
```

### 5. Make Nullable Explicit

```php
// Good ✅
$table->string('middle_name')->nullable();

// Bad ❌
$table->string('middle_name'); // Is it required?
```

## Next Steps

- [Model Generator](model.md)
- [Schema Builder Reference](../database/schema-builder.md)
- [Database Commands](../database-commands.md)
- [Running Migrations](../database/migrations.md)
