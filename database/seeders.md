# Database Seeders

Seeders allow you to populate your database with test or initial data.

## Creating Seeders

### Generate Seeder

```bash
# Create seeder
php neo make:seeder UserSeeder

# Create from model
php neo make:seeder ProductSeeder --model=Product
```

### Seeder Structure

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\User;
use NeoPhp\Database\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create records
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);
    }
}
```

## Running Seeders

```bash
# Run all seeders (DatabaseSeeder)
php neo db:seed

# Run specific seeder
php neo db:seed --class=UserSeeder

# Run multiple seeders
php neo db:seed --class=UserSeeder,ProductSeeder,CategorySeeder

# Force in production
php neo db:seed --force
```

## Database Seeder

Main seeder that calls other seeders:

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            TagSeeder::class,
        ]);
    }
}
```

## Seeding Methods

### Using Models

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create single record
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
        ]);
        
        // Create multiple records
        $users = [
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
            ],
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
```

### Using Query Builder

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use NeoPhp\Database\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            'name' => 'Product 1',
            'price' => 99.99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Bulk insert
        $products = [];
        for ($i = 1; $i <= 100; $i++) {
            $products[] = [
                'name' => "Product {$i}",
                'price' => rand(10, 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('products')->insert($products);
    }
}
```

### Using Factories

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 products using factory
        Product::factory(50)->create();
        
        // Create with specific attributes
        Product::factory(10)->create([
            'status' => 'published',
            'featured' => true,
        ]);
        
        // Create different states
        Product::factory(20)->active()->create();
        Product::factory(10)->inactive()->create();
    }
}
```

## Factory Definitions

### Creating Factory

```bash
php neo make:factory ProductFactory --model=Product
```

### Factory Structure

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
            'slug' => $this->faker->slug(),
            'sku' => $this->faker->unique()->ean13(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->paragraph(),
            'in_stock' => $this->faker->boolean(80),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
        ];
    }
    
    /**
     * Indicate product is active
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'in_stock' => true,
            ];
        });
    }
    
    /**
     * Indicate product is inactive
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'archived',
                'in_stock' => false,
            ];
        });
    }
}
```

## Faker Data Types

```php
// Personal
$this->faker->name()                    // John Doe
$this->faker->firstName()               // John
$this->faker->lastName()                // Doe
$this->faker->email()                   // john@example.com
$this->faker->phoneNumber()             // +1234567890

// Address
$this->faker->address()                 // 123 Main St, City
$this->faker->city()                    // New York
$this->faker->country()                 // United States
$this->faker->postcode()               // 12345

// Text
$this->faker->word()                    // lorem
$this->faker->sentence()                // Lorem ipsum dolor sit amet.
$this->faker->paragraph()               // Long paragraph...
$this->faker->text(200)                // Text with max 200 chars

// Numbers
$this->faker->randomDigit()            // 7
$this->faker->randomNumber(5)          // 12345
$this->faker->numberBetween(1, 100)    // 42
$this->faker->randomFloat(2, 0, 1000)  // 123.45

// Internet
$this->faker->url()                     // https://example.com
$this->faker->slug()                    // lorem-ipsum-dolor
$this->faker->userName()                // john.doe
$this->faker->ipv4()                    // 192.168.1.1

// Date/Time
$this->faker->dateTime()                // DateTime object
$this->faker->date()                    // 2024-01-15
$this->faker->time()                    // 14:30:00
$this->faker->dateTimeBetween('-1 year', 'now')

// Boolean
$this->faker->boolean()                 // true/false
$this->faker->boolean(70)               // 70% true, 30% false

// Arrays
$this->faker->randomElement(['a', 'b', 'c'])
$this->faker->randomElements(['a', 'b', 'c'], 2)

// Unique values
$this->faker->unique()->email()
$this->faker->unique()->userName()

// Optional values (null or value)
$this->faker->optional()->phoneNumber()
```

## Complete Seeder Examples

### User Seeder with Roles

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        // Create editor users
        User::factory(5)->create([
            'role' => 'editor',
            'email_verified_at' => now(),
        ]);
        
        // Create regular users
        User::factory(50)->create();
    }
}
```

### E-commerce Seeder

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\{Category, Brand, Product, Tag};

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [];
        $categoryNames = ['Electronics', 'Clothing', 'Books', 'Home', 'Sports'];
        
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'name' => $name,
                'slug' => strtolower($name),
            ]);
        }
        
        // Create brands
        $brands = Brand::factory(20)->create();
        
        // Create tags
        $tags = Tag::factory(30)->create();
        
        // Create products
        foreach ($categories as $category) {
            $products = Product::factory(20)->create([
                'category_id' => $category->id,
                'brand_id' => $brands->random()->id,
            ]);
            
            // Attach random tags to each product
            foreach ($products as $product) {
                $product->tags()->attach(
                    $tags->random(rand(2, 5))->pluck('id')->toArray()
                );
            }
        }
    }
}
```

### Blog Seeder

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\{User, Category, Post, Tag, Comment};

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@blog.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);
        
        $authors = User::factory(5)->create(['role' => 'author']);
        $users = User::factory(50)->create();
        
        // Create categories
        $categories = Category::factory(10)->create();
        
        // Create tags
        $tags = Tag::factory(30)->create();
        
        // Create posts
        foreach ($authors as $author) {
            $posts = Post::factory(20)->create([
                'user_id' => $author->id,
                'category_id' => $categories->random()->id,
            ]);
            
            foreach ($posts as $post) {
                // Attach tags
                $post->tags()->attach(
                    $tags->random(rand(3, 7))->pluck('id')
                );
                
                // Create comments
                Comment::factory(rand(0, 15))->create([
                    'post_id' => $post->id,
                    'user_id' => $users->random()->id,
                ]);
            }
        }
    }
}
```

### Settings Seeder

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use NeoPhp\Database\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'My Website'],
            ['key' => 'site_description', 'value' => 'Welcome to my website'],
            ['key' => 'items_per_page', 'value' => '15'],
            ['key' => 'enable_comments', 'value' => '1'],
            ['key' => 'enable_registration', 'value' => '1'],
            ['key' => 'maintenance_mode', 'value' => '0'],
        ];
        
        DB::table('settings')->insert($settings);
    }
}
```

## Seeder Organization

### Calling Other Seeders

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Call seeders in order
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
        
        // Conditional seeding
        if (app()->environment('local')) {
            $this->call([
                TestDataSeeder::class,
            ]);
        }
    }
}
```

### Truncating Tables

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use NeoPhp\Database\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Truncate tables
        DB::table('users')->truncate();
        DB::table('products')->truncate();
        DB::table('orders')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Run seeders
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
```

## Progress Feedback

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating products...');
        
        Product::factory(100)->create();
        
        $this->command->info('Products created successfully!');
    }
}
```

## Chunked Seeding

```php
<?php

namespace Database\Seeders;

use NeoPhp\Database\Seeder;
use NeoPhp\Database\DB;

class LargeDataSeeder extends Seeder
{
    public function run(): void
    {
        $chunkSize = 1000;
        $totalRecords = 100000;
        
        for ($i = 0; $i < $totalRecords; $i += $chunkSize) {
            $records = [];
            
            for ($j = 0; $j < $chunkSize && ($i + $j) < $totalRecords; $j++) {
                $records[] = [
                    'name' => "Record " . ($i + $j),
                    'value' => rand(1, 1000),
                    'created_at' => now(),
                ];
            }
            
            DB::table('large_table')->insert($records);
            
            $this->command->info("Inserted " . ($i + $chunkSize) . " records");
        }
    }
}
```

## Best Practices

### 1. Use Factories for Test Data

```php
// Good ✅
Product::factory(100)->create();

// Bad ❌ - Hard to maintain
for ($i = 0; $i < 100; $i++) {
    Product::create([...]);
}
```

### 2. Organize Seeders Logically

```php
// Good ✅
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // Users first
            CategorySeeder::class,      // Then categories
            ProductSeeder::class,       // Then products
            OrderSeeder::class,         // Then orders
        ]);
    }
}
```

### 3. Handle Foreign Keys

```php
// Good ✅
Product::factory(50)->create([
    'category_id' => Category::factory(),  // Creates category automatically
]);

// Or
$category = Category::first();
Product::factory(50)->create([
    'category_id' => $category->id,
]);
```

### 4. Use Transactions

```php
DB::transaction(function () {
    User::factory(100)->create();
    Product::factory(500)->create();
});
```

### 5. Provide Feedback

```php
$this->command->info('Seeding users...');
User::factory(100)->create();

$this->command->info('Seeding products...');
Product::factory(500)->create();

$this->command->info('Database seeded successfully!');
```

## Testing with Seeders

```php
<?php

namespace Tests;

use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;

class ExampleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        // Run seeders before each test
        $this->seed(DatabaseSeeder::class);
    }
    
    public function test_example()
    {
        // Test with seeded data
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
        ]);
    }
}
```

## Next Steps

- [Migrations](migrations.md)
- [Query Builder](query-builder.md)
- [Database Getting Started](getting-started.md)
- [Testing](../advanced/testing.md)
