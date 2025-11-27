# Database Commands

NeoPhp provides powerful database commands for managing migrations, seeds, and database operations.

## Migration Commands

### Run Migrations

```bash
# Run all pending migrations
php neo migrate

# Run migrations with output
php neo migrate --verbose

# Run migrations for specific environment
php neo migrate --env=production

# Pretend mode (show SQL without executing)
php neo migrate --pretend
```

### Rollback Migrations

```bash
# Rollback last batch
php neo migrate:rollback

# Rollback specific number of steps
php neo migrate:rollback --step=2

# Rollback specific batch
php neo migrate:rollback --batch=3

# Rollback all migrations
php neo migrate:reset
```

### Refresh Database

```bash
# Rollback all and re-run migrations
php neo migrate:refresh

# Refresh and seed
php neo migrate:refresh --seed

# Refresh with specific seeder
php neo migrate:refresh --seed --class=DatabaseSeeder
```

### Fresh Database

```bash
# Drop all tables and re-run migrations
php neo migrate:fresh

# Fresh with seeding
php neo migrate:fresh --seed
```

### Migration Status

```bash
# Show migration status
php neo migrate:status

# Sample output:
# +------+------------------------------------------------+-------+
# | Ran? | Migration                                      | Batch |
# +------+------------------------------------------------+-------+
# | Yes  | 2024_01_01_000000_create_users_table          | 1     |
# | Yes  | 2024_01_02_000000_create_products_table       | 1     |
# | No   | 2024_01_03_000000_create_orders_table         |       |
# +------+------------------------------------------------+-------+
```

### Install Migration Table

```bash
# Create migrations table
php neo migrate:install
```

## Seeder Commands

### Run Seeders

```bash
# Run DatabaseSeeder
php neo db:seed

# Run specific seeder
php neo db:seed --class=UserSeeder

# Run multiple seeders
php neo db:seed --class=UserSeeder,ProductSeeder,CategorySeeder

# Force seeding in production
php neo db:seed --force
```

### Generate Seeder

```bash
# Create seeder class
php neo make:seeder ProductSeeder

# Create seeder from model
php neo make:seeder ProductSeeder --model=Product
```

Generated seeder:

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Sample Product',
            'price' => 99.99,
            'description' => 'This is a sample product',
        ]);
        
        // Or use factory
        Product::factory(50)->create();
    }
}
```

## Database Inspection

### Show Tables

```bash
# List all tables
php neo db:tables

# Sample output:
# users
# products
# categories
# orders
# order_items
```

### Table Structure

```bash
# Show table structure
php neo db:table users

# Sample output:
# +------------------+--------------+------+-----+---------+----------------+
# | Field            | Type         | Null | Key | Default | Extra          |
# +------------------+--------------+------+-----+---------+----------------+
# | id               | bigint(20)   | NO   | PRI | NULL    | auto_increment |
# | name             | varchar(255) | NO   |     | NULL    |                |
# | email            | varchar(255) | NO   | UNI | NULL    |                |
# | password         | varchar(255) | NO   |     | NULL    |                |
# | created_at       | timestamp    | YES  |     | NULL    |                |
# | updated_at       | timestamp    | YES  |     | NULL    |                |
# +------------------+--------------+------+-----+---------+----------------+
```

### Show Indexes

```bash
# Show table indexes
php neo db:indexes products

# Sample output:
# products_name_index
# products_sku_unique
# products_category_id_foreign
```

### Database Size

```bash
# Show database size
php neo db:size

# Sample output:
# Database: neophp_db
# Size: 45.2 MB
# Tables: 15
```

## Database Backup

### Backup Database

```bash
# Backup to default location
php neo db:backup

# Backup to specific file
php neo db:backup --file=backup_2024_01_15.sql

# Compress backup
php neo db:backup --compress

# Backup specific tables
php neo db:backup --tables=users,products,orders
```

### Restore Database

```bash
# Restore from backup
php neo db:restore backup_2024_01_15.sql

# Force restore without confirmation
php neo db:restore backup.sql --force
```

## Database Utilities

### Truncate Tables

```bash
# Truncate single table
php neo db:truncate users

# Truncate multiple tables
php neo db:truncate users,products,orders

# Truncate all tables
php neo db:truncate --all

# Force in production
php neo db:truncate --all --force
```

### Drop Tables

```bash
# Drop single table
php neo db:drop temp_table

# Drop multiple tables
php neo db:drop temp1,temp2,temp3

# Drop with foreign key check disabled
php neo db:drop temp_table --force
```

### Test Connection

```bash
# Test database connection
php neo db:test

# Sample output:
# ✓ Database connection successful
# Host: localhost
# Database: neophp_db
# Driver: mysql
# Version: 8.0.32
```

### Show Queries

```bash
# Enable query logging
php neo db:query-log

# Run your application...

# View queries
php neo db:queries

# Sample output:
# SELECT * FROM users WHERE id = 1 [0.5ms]
# INSERT INTO products (name, price) VALUES (?, ?) [1.2ms]
# UPDATE orders SET status = ? WHERE id = ? [0.8ms]
```

## Migration Path Management

### Specific Path

```bash
# Run migrations from specific path
php neo migrate --path=database/migrations/2024

# Run migrations from multiple paths
php neo migrate --path=database/migrations/core --path=database/migrations/plugins
```

### Realpath

```bash
# Use absolute path
php neo migrate --realpath=/var/www/app/migrations
```

## Database Factory Commands

### Generate Factory

```bash
# Create factory for model
php neo make:factory ProductFactory --model=Product
```

Generated factory:

```php
<?php

namespace Database\Factories;

use NeoPhp\Database\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected string $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->productName(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->paragraph(),
            'in_stock' => $this->faker->boolean(80),
        ];
    }
}
```

### Use Factory

```php
// In seeder or tests
use App\Models\Product;

// Create single record
$product = Product::factory()->create();

// Create multiple records
$products = Product::factory(50)->create();

// Create with attributes
$product = Product::factory()->create([
    'name' => 'Special Product',
    'price' => 99.99,
]);

// Make without saving
$product = Product::factory()->make();
```

## Environment-Specific Commands

### Production Safety

Most destructive commands require confirmation in production:

```bash
# Requires confirmation
php neo migrate:refresh  # Are you sure?

# Skip confirmation
php neo migrate:refresh --force
```

### Multiple Databases

```bash
# Specify database connection
php neo migrate --database=mysql

# Use different connection for seeding
php neo db:seed --database=mongodb
```

## Batch Operations

### Chain Commands

```bash
# Refresh database and seed
php neo migrate:refresh --seed

# Fresh install with specific seeder
php neo migrate:fresh --seed --class=InitialSeeder
```

## Advanced Usage

### Custom Migration Table

Configure in `config/database.php`:

```php
'migrations' => 'custom_migrations_table',
```

Then run:

```bash
php neo migrate:install --table=custom_migrations_table
```

### Migration Locking

Prevent concurrent migrations:

```bash
# Migrations are automatically locked
# If locked, you'll see:
# Migration is already running

# Force unlock if needed
php neo migrate:unlock
```

### Isolated Migrations

Run migrations in isolation (useful for testing):

```bash
php neo migrate --isolated
```

## Troubleshooting

### Connection Refused

```bash
# Check connection
php neo db:test

# Verify .env settings
cat .env | grep DB_

# Common fixes:
# - Check MySQL/PostgreSQL is running
# - Verify credentials
# - Check host and port
```

### Migration Already Ran

```bash
# Check status
php neo migrate:status

# Rollback and retry
php neo migrate:rollback
php neo migrate
```

### Foreign Key Errors

```bash
# Disable foreign key checks temporarily
php neo db:migrate --no-foreign-keys

# Or in migration:
Schema::disableForeignKeyConstraints();
// Your migrations...
Schema::enableForeignKeyConstraints();
```

### Out of Memory

```bash
# Increase memory limit
php -d memory_limit=512M neo migrate

# Or seed in batches
php neo db:seed --class=UserSeeder --batch=1000
```

## Best Practices

### 1. Always Test Migrations

```bash
# Test in development first
php neo migrate

# Check status
php neo migrate:status

# Test rollback
php neo migrate:rollback

# Then deploy to production
```

### 2. Backup Before Migration

```bash
# Always backup production
php neo db:backup --compress

# Then migrate
php neo migrate --force
```

### 3. Use Transactions

Most databases support migration transactions:

```php
// In migration
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Changes automatically wrapped in transaction
    });
}
```

### 4. Seed Safely

```bash
# Use --class for specific seeders
php neo db:seed --class=InitialDataSeeder

# Don't seed everything in production
# Be selective about what data to seed
```

### 5. Monitor Query Performance

```bash
# Enable logging
php neo db:query-log

# Review slow queries
php neo db:queries --slow --threshold=100ms
```

## Next Steps

- [Migrations Guide](../database/migrations.md)
- [Database Seeders](../database/seeders.md)
- [Schema Builder](../database/schema-builder.md)
- [Query Builder](../database/query-builder.md)
