# Migrations

Migrations are version control for your database, allowing you to define and share database schema changes.

## Creating Migrations

### Generate Migration

```bash
# Create migration
php neo make:migration create_users_table

# Create with table creation
php neo make:migration create_users_table --create=users

# Create for table modification
php neo make:migration add_avatar_to_users --table=users

# Generate from model
php neo make:migration --from-model=User
```

### Migration Structure

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
        $this->schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('users');
    }
};
```

## Running Migrations

### Execute Migrations

```bash
# Run all pending migrations
php neo migrate

# Run with output
php neo migrate --verbose

# Pretend (show SQL without executing)
php neo migrate --pretend

# Force in production
php neo migrate --force
```

### Rollback Migrations

```bash
# Rollback last batch
php neo migrate:rollback

# Rollback specific steps
php neo migrate:rollback --step=2

# Rollback all
php neo migrate:reset

# Rollback and re-run
php neo migrate:refresh

# Refresh with seeding
php neo migrate:refresh --seed
```

### Fresh Migration

```bash
# Drop all tables and re-run
php neo migrate:fresh

# Fresh with seeding
php neo migrate:fresh --seed
```

## Creating Tables

### Basic Table

```php
$this->schema->create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->timestamps();
});
```

### Complete Example

```php
$this->schema->create('products', function (Blueprint $table) {
    // Primary key
    $table->id();
    
    // Basic fields
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('sku', 100)->unique();
    
    // Prices
    $table->decimal('price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    
    // Content
    $table->text('description')->nullable();
    $table->longText('specifications')->nullable();
    
    // Media
    $table->string('image')->nullable();
    $table->json('images')->nullable();
    
    // Stock
    $table->integer('stock_quantity')->unsigned()->default(0);
    $table->boolean('in_stock')->default(true);
    
    // Foreign keys
    $table->foreignId('category_id')
          ->constrained()
          ->onDelete('cascade');
    
    $table->foreignId('brand_id')
          ->nullable()
          ->constrained()
          ->onDelete('set null');
    
    // Status
    $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
    
    // SEO
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    
    // Timestamps
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index('status');
    $table->index(['category_id', 'status']);
    $table->fulltext(['name', 'description']);
});
```

## Column Types

### String Types

```php
$table->char('code', 4);              // CHAR(4)
$table->string('name');               // VARCHAR(255)
$table->string('email', 320);         // VARCHAR(320)
$table->text('description');          // TEXT
$table->mediumText('content');        // MEDIUMTEXT
$table->longText('article');          // LONGTEXT
```

### Numeric Types

```php
$table->tinyInteger('status');        // TINYINT
$table->smallInteger('votes');        // SMALLINT
$table->mediumInteger('views');       // MEDIUMINT
$table->integer('count');             // INT
$table->bigInteger('large_number');   // BIGINT

$table->decimal('price', 8, 2);       // DECIMAL(8,2)
$table->float('rating');              // FLOAT
$table->double('lat', 10, 7);         // DOUBLE(10,7)

$table->unsignedTinyInteger('age');   // UNSIGNED TINYINT
$table->unsignedInteger('points');    // UNSIGNED INT
$table->unsignedBigInteger('id');     // UNSIGNED BIGINT
```

### Date and Time

```php
$table->date('birth_date');           // DATE
$table->time('alarm_time');           // TIME
$table->datetime('published_at');     // DATETIME
$table->timestamp('created_at');      // TIMESTAMP
$table->year('year');                 // YEAR

// Auto timestamps
$table->timestamps();                 // created_at & updated_at
$table->timestampsTz();              // with timezone
$table->softDeletes();               // deleted_at
$table->softDeletesTz();             // deleted_at with timezone
```

### Other Types

```php
$table->boolean('active');            // TINYINT(1)
$table->json('metadata');             // JSON
$table->jsonb('data');                // JSONB (PostgreSQL)
$table->binary('data');               // BLOB
$table->enum('status', ['draft', 'published']);
$table->set('roles', ['admin', 'user']);
```

### Special Columns

```php
$table->id();                         // Auto-increment BIGINT UNSIGNED
$table->uuid('id');                   // UUID
$table->ulid('id');                   // ULID
$table->foreignId('user_id');         // BIGINT UNSIGNED for foreign keys
$table->morphs('taggable');           // taggable_id, taggable_type
$table->uuidMorphs('taggable');       // UUID morphs
$table->rememberToken();              // remember_token VARCHAR(100)
```

## Column Modifiers

```php
$table->string('email')->nullable();
$table->string('name')->default('Guest');
$table->integer('votes')->unsigned();
$table->string('slug')->unique();
$table->string('bio')->after('email');
$table->string('name')->first();
$table->text('description')->comment('Product description');
$table->string('name')->charset('utf8mb4');
$table->string('name')->collation('utf8mb4_unicode_ci');
$table->integer('id')->autoIncrement();
$table->integer('id')->from(1000);
$table->boolean('active')->invisible();
$table->string('name')->storedAs('first_name || \' \' || last_name');
$table->string('name')->virtualAs('first_name || \' \' || last_name');
```

## Indexes

### Creating Indexes

```php
// Single column
$table->string('email')->index();
$table->string('email')->unique();
$table->text('body')->fulltext();

// Multiple columns
$table->index(['user_id', 'created_at']);
$table->unique(['email', 'tenant_id']);

// Named indexes
$table->index('email', 'users_email_index');
$table->unique(['email', 'tenant_id'], 'unique_email_tenant');

// After column definition
$table->string('email');
$table->index('email');
```

### Dropping Indexes

```php
$table->dropIndex('users_email_index');
$table->dropIndex(['email']);
$table->dropUnique('users_email_unique');
$table->dropUnique(['email']);
$table->dropPrimary('users_id_primary');
```

### Index Types

```php
$table->index('column');              // Regular index
$table->unique('column');             // Unique index
$table->fulltext('column');           // Full-text index
$table->spatialIndex('location');     // Spatial index (MySQL)
```

## Foreign Keys

### Creating Foreign Keys

```php
// Simple foreign key
$table->foreignId('user_id')
      ->constrained()
      ->onDelete('cascade');

// Custom reference table
$table->foreignId('author_id')
      ->constrained('users')
      ->onDelete('cascade');

// Manual foreign key
$table->unsignedBigInteger('category_id');
$table->foreign('category_id')
      ->references('id')
      ->on('categories')
      ->onDelete('cascade')
      ->onUpdate('cascade');
```

### Foreign Key Actions

```php
// On delete actions
->onDelete('cascade')     // Delete related records
->onDelete('set null')    // Set foreign key to null
->onDelete('restrict')    // Prevent deletion
->onDelete('no action')   // Database handles it

// On update actions
->onUpdate('cascade')     // Update related records
->onUpdate('restrict')    // Prevent update
```

### Dropping Foreign Keys

```php
$table->dropForeign('posts_user_id_foreign');
$table->dropForeign(['user_id']);
$table->dropConstrainedForeignId('user_id');
```

## Modifying Tables

### Adding Columns

```php
$this->schema->table('users', function (Blueprint $table) {
    $table->string('phone')->nullable();
    $table->text('bio')->nullable();
    $table->timestamp('last_login_at')->nullable();
});
```

### Modifying Columns

```php
$this->schema->table('users', function (Blueprint $table) {
    // Change column type
    $table->string('email', 320)->change();
    
    // Make nullable
    $table->string('phone')->nullable()->change();
    
    // Change default
    $table->boolean('active')->default(true)->change();
});
```

### Renaming Columns

```php
$this->schema->table('users', function (Blueprint $table) {
    $table->renameColumn('old_name', 'new_name');
});
```

### Dropping Columns

```php
$this->schema->table('users', function (Blueprint $table) {
    $table->dropColumn('phone');
    
    // Drop multiple columns
    $table->dropColumn(['phone', 'bio', 'avatar']);
});
```

### Renaming Tables

```php
$this->schema->rename('old_table_name', 'new_table_name');
```

### Dropping Tables

```php
$this->schema->drop('users');
$this->schema->dropIfExists('users');
```

## Table Options

```php
$this->schema->create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    
    // Set engine
    $table->engine = 'InnoDB';
    
    // Set charset
    $table->charset = 'utf8mb4';
    
    // Set collation
    $table->collation = 'utf8mb4_unicode_ci';
    
    // Temporary table
    $table->temporary();
    
    // Add comment
    $table->comment('User accounts table');
});
```

## Checking Table/Column Existence

```php
if ($this->schema->hasTable('users')) {
    // Table exists
}

if ($this->schema->hasColumn('users', 'email')) {
    // Column exists
}

if ($this->schema->hasColumns('users', ['name', 'email'])) {
    // All columns exist
}
```

## Raw SQL in Migrations

```php
$this->schema->create('users', function (Blueprint $table) {
    $table->id();
    
    // Raw column definition
    $table->raw('name VARCHAR(255) NOT NULL');
    
    // Add raw SQL
    DB::statement('ALTER TABLE users ADD FULLTEXT(name, email)');
});
```

## Migration Dependencies

### Specify Run Order

Migrations run in filename order (timestamp). For dependencies:

```php
// 2024_01_01_000001_create_users_table.php
// 2024_01_01_000002_create_profiles_table.php (depends on users)
```

### Check Before Dropping

```php
public function down(): void
{
    // Disable foreign key checks
    Schema::disableForeignKeyConstraints();
    
    $this->schema->dropIfExists('profiles');
    $this->schema->dropIfExists('users');
    
    // Re-enable foreign key checks
    Schema::enableForeignKeyConstraints();
}
```

## Squashing Migrations

Combine multiple migrations:

```bash
# Squash all migrations into one
php neo schema:dump

# Squash and keep migrations
php neo schema:dump --prune
```

## Best Practices

### 1. Always Provide Down Method

```php
// Good ✅
public function down(): void
{
    $this->schema->dropIfExists('users');
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

// Ensures referential integrity
```

### 3. Add Indexes to Queried Columns

```php
$table->index('email');
$table->index('status');
$table->index(['user_id', 'created_at']);
```

### 4. Use Appropriate Data Types

```php
// Good ✅
$table->boolean('active');
$table->decimal('price', 10, 2);
$table->json('metadata');

// Bad ❌
$table->string('active');  // Should be boolean
$table->float('price');    // Should be decimal for currency
```

### 5. Test Rollbacks

```bash
# Always test that rollback works
php neo migrate
php neo migrate:rollback
php neo migrate
```

### 6. Don't Modify Published Migrations

```php
// Bad ❌ - Don't edit migrations that have run
// Create a new migration instead

php neo make:migration add_phone_to_users --table=users
```

### 7. Use Transactions

Most database systems support migration transactions automatically, but you can be explicit:

```php
public function up(): void
{
    DB::transaction(function () {
        // Your schema changes
    });
}
```

## Troubleshooting

### Migration Table Not Found

```bash
php neo migrate:install
```

### Foreign Key Errors

```php
// Disable temporarily
Schema::disableForeignKeyConstraints();
// Your migrations
Schema::enableForeignKeyConstraints();
```

### Column Already Exists

```php
if (!$this->schema->hasColumn('users', 'phone')) {
    $this->schema->table('users', function (Blueprint $table) {
        $table->string('phone')->nullable();
    });
}
```

## Next Steps

- [Schema Builder](schema-builder.md)
- [Database Commands](../cli-tools/database-commands.md)
- [Query Builder](query-builder.md)
- [Seeders](seeders.md)
