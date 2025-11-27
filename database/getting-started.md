# Database - Getting Started

NeoPhp provides a powerful database layer with query builder, migrations, and ORM features.

## Database Configuration

### Setting Up Connection

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=neophp_db
DB_USERNAME=root
DB_PASSWORD=secret
```

### Multiple Connections

Define multiple connections in `config/database.php`:

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'neophp'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
        ],
        
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('PGSQL_HOST', 'localhost'),
            'port' => env('PGSQL_PORT', 5432),
            'database' => env('PGSQL_DATABASE', 'neophp'),
            'username' => env('PGSQL_USERNAME', 'postgres'),
            'password' => env('PGSQL_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix' => '',
        ],
    ],
];
```

## Using Database

### Raw Queries

```php
use NeoPhp\Database\DB;

// Select query
$users = DB::select('SELECT * FROM users WHERE active = ?', [1]);

// Insert query
DB::insert('INSERT INTO users (name, email) VALUES (?, ?)', ['John', 'john@example.com']);

// Update query
DB::update('UPDATE users SET active = ? WHERE id = ?', [1, 5]);

// Delete query
DB::delete('DELETE FROM users WHERE id = ?', [5]);

// General statement
DB::statement('DROP TABLE IF EXISTS temp_users');
```

### Query Builder

```php
use NeoPhp\Database\DB;

// Select all
$users = DB::table('users')->get();

// Select with conditions
$users = DB::table('users')
    ->where('active', 1)
    ->where('age', '>', 18)
    ->get();

// Select single row
$user = DB::table('users')->where('id', 1)->first();

// Select specific columns
$users = DB::table('users')->select('name', 'email')->get();

// Order and limit
$users = DB::table('users')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### Inserts

```php
// Insert single row
DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT),
]);

// Insert and get ID
$id = DB::table('users')->insertGetId([
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
]);

// Insert multiple rows
DB::table('users')->insert([
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com'],
    ['name' => 'User 3', 'email' => 'user3@example.com'],
]);
```

### Updates

```php
// Update records
DB::table('users')
    ->where('id', 1)
    ->update(['name' => 'John Updated']);

// Update multiple columns
DB::table('users')
    ->where('active', 0)
    ->update([
        'active' => 1,
        'updated_at' => now(),
    ]);

// Increment/Decrement
DB::table('users')->where('id', 1)->increment('points');
DB::table('users')->where('id', 1)->increment('points', 5);
DB::table('users')->where('id', 1)->decrement('credits');
```

### Deletes

```php
// Delete records
DB::table('users')->where('id', 1)->delete();

// Delete with multiple conditions
DB::table('users')
    ->where('active', 0)
    ->where('created_at', '<', now()->subYear())
    ->delete();

// Truncate table
DB::table('users')->truncate();
```

## Using Models

### Create Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('users')]
#[Timestamps]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $name;
    
    #[Field(type: 'string', length: 255)]
    #[Unique]
    public string $email;
}
```

### Model Operations

```php
use App\Models\User;

// Find by ID
$user = User::find(1);

// Find or fail (throws exception if not found)
$user = User::findOrFail(1);

// Get all records
$users = User::all();

// Query with conditions
$users = User::where('active', 1)->get();

// Create new record
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Update record
$user = User::find(1);
$user->name = 'John Updated';
$user->save();

// Delete record
$user = User::find(1);
$user->delete();
```

## Transactions

### Basic Transaction

```php
use NeoPhp\Database\DB;

DB::beginTransaction();

try {
    DB::table('users')->insert(['name' => 'John']);
    DB::table('profiles')->insert(['user_id' => DB::lastInsertId()]);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

### Transaction Closure

```php
DB::transaction(function () {
    DB::table('users')->insert(['name' => 'John']);
    DB::table('profiles')->insert(['user_id' => DB::lastInsertId()]);
});
```

### Manual Transactions

```php
DB::beginTransaction();

// Your queries...

DB::commit();
// or
DB::rollback();
```

## Pagination

### Basic Pagination

```php
// Paginate query builder
$users = DB::table('users')->paginate(15);

// Paginate model
$users = User::paginate(15);

// Custom per page
$users = User::paginate(request('per_page', 20));
```

### Simple Pagination

```php
// Simple pagination (Next/Previous only)
$users = User::simplePaginate(15);
```

### Using in Views

```php
<table>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user->name ?></td>
            <td><?= $user->email ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Pagination links -->
<?= $users->links() ?>

<!-- Pagination info -->
<p>
    Showing <?= $users->firstItem() ?> to <?= $users->lastItem() ?> 
    of <?= $users->total() ?> results
</p>
```

## Aggregates

```php
use NeoPhp\Database\DB;

// Count
$count = DB::table('users')->count();
$active = DB::table('users')->where('active', 1)->count();

// Max/Min
$max = DB::table('orders')->max('total');
$min = DB::table('orders')->min('total');

// Sum
$total = DB::table('orders')->sum('total');

// Average
$average = DB::table('products')->avg('price');

// Exists
$exists = DB::table('users')->where('email', 'john@example.com')->exists();
```

## Joins

### Inner Join

```php
$users = DB::table('users')
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->select('users.*', 'profiles.bio')
    ->get();
```

### Left Join

```php
$users = DB::table('users')
    ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
    ->select('users.*', 'profiles.bio')
    ->get();
```

### Multiple Joins

```php
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->select('posts.*', 'users.name as author', 'categories.name as category')
    ->get();
```

## Grouping and Ordering

```php
// Group by
$users = DB::table('orders')
    ->select('user_id', DB::raw('COUNT(*) as order_count'))
    ->groupBy('user_id')
    ->get();

// Having
$users = DB::table('orders')
    ->select('user_id', DB::raw('COUNT(*) as order_count'))
    ->groupBy('user_id')
    ->having('order_count', '>', 5)
    ->get();

// Order by
$users = DB::table('users')
    ->orderBy('created_at', 'desc')
    ->orderBy('name', 'asc')
    ->get();

// Random order
$users = DB::table('users')->inRandomOrder()->limit(10)->get();
```

## Advanced Where Clauses

### Multiple Conditions

```php
// AND conditions
$users = DB::table('users')
    ->where('active', 1)
    ->where('verified', 1)
    ->get();

// OR conditions
$users = DB::table('users')
    ->where('role', 'admin')
    ->orWhere('role', 'editor')
    ->get();
```

### Where In

```php
$users = DB::table('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->get();

$users = DB::table('users')
    ->whereNotIn('status', ['banned', 'deleted'])
    ->get();
```

### Where Between

```php
$orders = DB::table('orders')
    ->whereBetween('total', [100, 500])
    ->get();

$orders = DB::table('orders')
    ->whereNotBetween('total', [0, 10])
    ->get();
```

### Where Null

```php
$users = DB::table('users')
    ->whereNull('deleted_at')
    ->get();

$users = DB::table('users')
    ->whereNotNull('email_verified_at')
    ->get();
```

### Where Like

```php
$users = DB::table('users')
    ->where('name', 'like', '%john%')
    ->get();
```

### Nested Where

```php
$users = DB::table('users')
    ->where('active', 1)
    ->where(function($query) {
        $query->where('role', 'admin')
              ->orWhere('role', 'editor');
    })
    ->get();
```

## Database Events

```php
use NeoPhp\Database\DB;

// Listen to query events
DB::listen(function($query, $bindings, $time) {
    logger()->debug("Query executed in {$time}ms", [
        'query' => $query,
        'bindings' => $bindings,
    ]);
});
```

## Connection Management

```php
// Get default connection
$connection = DB::connection();

// Get specific connection
$mysql = DB::connection('mysql');
$pgsql = DB::connection('pgsql');

// Use specific connection
$users = DB::connection('mysql')
    ->table('users')
    ->get();

// Disconnect
DB::disconnect('mysql');

// Reconnect
DB::reconnect('mysql');
```

## Best Practices

### 1. Use Parameter Binding

```php
// Good ✅
$users = DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// Bad ❌ - SQL Injection risk
$users = DB::select("SELECT * FROM users WHERE email = '$email'");
```

### 2. Use Transactions for Multiple Operations

```php
// Good ✅
DB::transaction(function() {
    $user = User::create([...]);
    $profile = Profile::create(['user_id' => $user->id]);
});

// Bad ❌ - No rollback on failure
$user = User::create([...]);
$profile = Profile::create(['user_id' => $user->id]);
```

### 3. Use Query Builder Over Raw SQL

```php
// Good ✅
$users = DB::table('users')->where('active', 1)->get();

// Acceptable ✓
$users = DB::select('SELECT * FROM users WHERE active = ?', [1]);
```

### 4. Index Frequently Queried Columns

```php
// In migration
$table->index('email');
$table->index('status');
$table->index(['user_id', 'created_at']);
```

### 5. Use Pagination for Large Datasets

```php
// Good ✅
$users = User::paginate(50);

// Bad ❌ - May cause memory issues
$users = User::all();
```

## Next Steps

- [Migrations](migrations.md)
- [Schema Builder](schema-builder.md)
- [Query Builder](query-builder.md)
- [Database Seeders](seeders.md)
