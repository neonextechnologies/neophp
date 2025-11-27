# Query Builder

The Query Builder provides a fluent interface for constructing database queries.

## Basic Queries

### Retrieving Results

```php
use NeoPhp\Database\DB;

// Get all records
$users = DB::table('users')->get();

// Get first record
$user = DB::table('users')->first();

// Get first or fail (throws exception)
$user = DB::table('users')->firstOrFail();

// Get single column value
$email = DB::table('users')->where('id', 1)->value('email');

// Get single column from first row
$name = DB::table('users')->value('name');

// Pluck column values
$names = DB::table('users')->pluck('name');

// Pluck with key
$users = DB::table('users')->pluck('name', 'id');
// Result: [1 => 'John', 2 => 'Jane', ...]
```

### Chunking Results

```php
// Process in chunks (memory efficient)
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});

// Stop chunking by returning false
DB::table('users')->chunk(100, function ($users) {
    // Process users
    
    if ($someCondition) {
        return false; // Stop processing
    }
});

// Lazy loading (one at a time)
DB::table('users')->lazy()->each(function ($user) {
    // Process user
});
```

### Cursor (Memory Efficient)

```php
foreach (DB::table('users')->cursor() as $user) {
    // Only one record in memory at a time
}
```

## Select Statements

### Basic Select

```php
// Select specific columns
$users = DB::table('users')
    ->select('name', 'email')
    ->get();

// Select with alias
$users = DB::table('users')
    ->select('name', 'email as user_email')
    ->get();

// Add select
$query = DB::table('users')->select('name');
$users = $query->addSelect('email')->get();
```

### Distinct

```php
$users = DB::table('users')
    ->select('role')
    ->distinct()
    ->get();
```

### Raw Expressions

```php
// Raw select
$users = DB::table('users')
    ->select(DB::raw('COUNT(*) as user_count, status'))
    ->groupBy('status')
    ->get();

// selectRaw
$orders = DB::table('orders')
    ->selectRaw('price * quantity as total')
    ->get();
```

## Where Clauses

### Basic Where

```php
// Simple where
$users = DB::table('users')
    ->where('active', 1)
    ->get();

// Where with operator
$users = DB::table('users')
    ->where('votes', '>', 100)
    ->get();

// Multiple conditions (AND)
$users = DB::table('users')
    ->where('active', 1)
    ->where('role', 'admin')
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

// Case-insensitive like (PostgreSQL)
$users = DB::table('users')
    ->where('name', 'ilike', '%john%')
    ->get();
```

### Where Date

```php
$orders = DB::table('orders')
    ->whereDate('created_at', '2024-01-15')
    ->get();

$orders = DB::table('orders')
    ->whereYear('created_at', '2024')
    ->get();

$orders = DB::table('orders')
    ->whereMonth('created_at', '01')
    ->get();

$orders = DB::table('orders')
    ->whereDay('created_at', '15')
    ->get();

$orders = DB::table('orders')
    ->whereTime('created_at', '>', '12:00:00')
    ->get();
```

### Where Column

```php
// Compare two columns
$users = DB::table('users')
    ->whereColumn('updated_at', '>', 'created_at')
    ->get();

$users = DB::table('users')
    ->whereColumn('first_name', 'last_name')
    ->get();
```

### Nested Where (Grouping)

```php
$users = DB::table('users')
    ->where('active', 1)
    ->where(function ($query) {
        $query->where('role', 'admin')
              ->orWhere('role', 'editor');
    })
    ->get();

// SQL: WHERE active = 1 AND (role = 'admin' OR role = 'editor')
```

### Where Exists

```php
$users = DB::table('users')
    ->whereExists(function ($query) {
        $query->select(DB::raw(1))
              ->from('orders')
              ->whereColumn('orders.user_id', 'users.id');
    })
    ->get();
```

### JSON Where

```php
// MySQL/PostgreSQL JSON queries
$users = DB::table('users')
    ->where('preferences->notifications', true)
    ->get();

$users = DB::table('users')
    ->where('settings->language', 'en')
    ->get();
```

## Ordering

```php
// Order by single column
$users = DB::table('users')
    ->orderBy('name')
    ->get();

// Order with direction
$users = DB::table('users')
    ->orderBy('created_at', 'desc')
    ->get();

// Multiple order by
$users = DB::table('users')
    ->orderBy('role', 'asc')
    ->orderBy('name', 'asc')
    ->get();

// Latest/Oldest (by created_at)
$users = DB::table('users')->latest()->get();
$users = DB::table('users')->oldest()->get();

// Random order
$users = DB::table('users')
    ->inRandomOrder()
    ->limit(10)
    ->get();

// Raw order by
$users = DB::table('users')
    ->orderByRaw('FIELD(status, "active", "pending", "inactive")')
    ->get();
```

## Limit and Offset

```php
// Limit
$users = DB::table('users')->limit(10)->get();

// Limit with offset
$users = DB::table('users')
    ->limit(10)
    ->offset(20)
    ->get();

// Take (alias for limit)
$users = DB::table('users')->take(5)->get();

// Skip (alias for offset)
$users = DB::table('users')
    ->skip(10)
    ->take(5)
    ->get();
```

## Grouping

```php
// Group by
$users = DB::table('orders')
    ->select('user_id', DB::raw('COUNT(*) as order_count'))
    ->groupBy('user_id')
    ->get();

// Group by multiple columns
$sales = DB::table('orders')
    ->select('year', 'month', DB::raw('SUM(total) as revenue'))
    ->groupBy('year', 'month')
    ->get();

// Having
$users = DB::table('orders')
    ->select('user_id', DB::raw('COUNT(*) as order_count'))
    ->groupBy('user_id')
    ->having('order_count', '>', 5)
    ->get();

// Having raw
$users = DB::table('orders')
    ->select('user_id', DB::raw('SUM(total) as total_spent'))
    ->groupBy('user_id')
    ->havingRaw('SUM(total) > 1000')
    ->get();
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

### Right Join

```php
$users = DB::table('users')
    ->rightJoin('profiles', 'users.id', '=', 'profiles.user_id')
    ->get();
```

### Cross Join

```php
$combinations = DB::table('colors')
    ->crossJoin('sizes')
    ->get();
```

### Advanced Joins

```php
// Join with multiple conditions
$users = DB::table('users')
    ->join('profiles', function ($join) {
        $join->on('users.id', '=', 'profiles.user_id')
             ->where('profiles.verified', 1);
    })
    ->get();

// Join with OR condition
$users = DB::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
             ->orOn('users.email', '=', 'contacts.email');
    })
    ->get();
```

### Join Sub-Query

```php
$latestPosts = DB::table('posts')
    ->select('user_id', DB::raw('MAX(created_at) as last_post_created_at'))
    ->groupBy('user_id');

$users = DB::table('users')
    ->joinSub($latestPosts, 'latest_posts', function ($join) {
        $join->on('users.id', '=', 'latest_posts.user_id');
    })
    ->get();
```

## Unions

```php
$first = DB::table('users')->where('role', 'admin');
$second = DB::table('users')->where('role', 'editor');

$users = $first->union($second)->get();

// Union all
$users = $first->unionAll($second)->get();
```

## Aggregates

```php
// Count
$count = DB::table('users')->count();
$active = DB::table('users')->where('active', 1)->count();

// Max
$max = DB::table('orders')->max('total');

// Min
$min = DB::table('orders')->min('total');

// Average
$average = DB::table('products')->avg('price');

// Sum
$total = DB::table('orders')->sum('total');

// Exists
$exists = DB::table('users')->where('email', 'john@example.com')->exists();

// Doesn't exist
$doesntExist = DB::table('users')->where('email', 'new@example.com')->doesntExist();
```

## Inserts

```php
// Insert single record
DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Insert and get ID
$id = DB::table('users')->insertGetId([
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
]);

// Insert multiple records
DB::table('users')->insert([
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com'],
    ['name' => 'User 3', 'email' => 'user3@example.com'],
]);

// Insert or ignore (MySQL)
DB::table('users')->insertOrIgnore([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

## Updates

```php
// Update single record
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

// Update or insert (upsert)
DB::table('users')->updateOrInsert(
    ['email' => 'john@example.com'],
    ['name' => 'John', 'active' => 1]
);

// Increment/Decrement
DB::table('users')->where('id', 1)->increment('points');
DB::table('users')->where('id', 1)->increment('points', 5);
DB::table('users')->where('id', 1)->decrement('credits');
DB::table('users')->where('id', 1)->decrement('credits', 3);

// Increment with additional update
DB::table('users')->where('id', 1)->increment('points', 1, [
    'updated_at' => now(),
]);
```

## Deletes

```php
// Delete records
DB::table('users')->where('id', 1)->delete();

// Delete with multiple conditions
DB::table('users')
    ->where('active', 0)
    ->where('created_at', '<', now()->subYear())
    ->delete();

// Truncate table (delete all and reset ID)
DB::table('users')->truncate();
```

## Pessimistic Locking

```php
// Shared lock (read lock)
$users = DB::table('users')
    ->where('votes', '>', 100)
    ->sharedLock()
    ->get();

// Exclusive lock (write lock)
$users = DB::table('users')
    ->where('votes', '>', 100)
    ->lockForUpdate()
    ->get();
```

## Debugging

```php
// Get SQL query
$sql = DB::table('users')
    ->where('active', 1)
    ->toSql();

// Get SQL with bindings
$query = DB::table('users')->where('active', 1);
dd($query->toSql(), $query->getBindings());

// Dump query
DB::table('users')->where('active', 1)->dd();
DB::table('users')->where('active', 1)->dump();
```

## Conditional Clauses

```php
$role = request('role');
$status = request('status');

$users = DB::table('users')
    ->when($role, function ($query, $role) {
        return $query->where('role', $role);
    })
    ->when($status, function ($query, $status) {
        return $query->where('status', $status);
    })
    ->get();

// With else
$sortBy = request('sort_by');

$users = DB::table('users')
    ->when($sortBy, function ($query, $sortBy) {
        return $query->orderBy($sortBy);
    }, function ($query) {
        return $query->orderBy('created_at', 'desc');
    })
    ->get();
```

## Sub-Queries

### Where Sub-Query

```php
$users = DB::table('users')
    ->where('points', '>', function ($query) {
        $query->select(DB::raw('AVG(points)'))
              ->from('users');
    })
    ->get();
```

### Select Sub-Query

```php
$users = DB::table('users')
    ->select('name', 'email')
    ->selectSub(function ($query) {
        $query->select(DB::raw('COUNT(*)'))
              ->from('orders')
              ->whereColumn('orders.user_id', 'users.id');
    }, 'order_count')
    ->get();
```

### From Sub-Query

```php
$avgPrices = DB::table('products')
    ->select('category_id', DB::raw('AVG(price) as avg_price'))
    ->groupBy('category_id');

$categories = DB::table(DB::raw("({$avgPrices->toSql()}) as avg_prices"))
    ->mergeBindings($avgPrices)
    ->where('avg_price', '>', 100)
    ->get();
```

## Pagination

```php
// Simple pagination
$users = DB::table('users')->paginate(15);

// Simple pagination (Next/Previous only)
$users = DB::table('users')->simplePaginate(15);

// Custom per page
$perPage = request('per_page', 20);
$users = DB::table('users')->paginate($perPage);

// Specific page
$users = DB::table('users')->paginate(15, ['*'], 'page', 2);
```

## Raw Methods

```php
// Raw select
DB::selectRaw('SELECT * FROM users WHERE active = ?', [1]);

// Raw insert
DB::insertRaw('INSERT INTO users (name, email) VALUES (?, ?)', ['John', 'john@example.com']);

// Raw update
DB::updateRaw('UPDATE users SET active = ? WHERE id = ?', [1, 5]);

// Raw delete
DB::deleteRaw('DELETE FROM users WHERE id = ?', [5]);

// Raw expression
$users = DB::table('users')
    ->select(DB::raw('COUNT(*) as user_count'))
    ->get();
```

## Best Practices

### 1. Use Parameter Binding

```php
// Good ✅
$users = DB::table('users')->where('email', $email)->get();

// Bad ❌ - SQL Injection risk
$users = DB::table('users')->whereRaw("email = '$email'")->get();
```

### 2. Use Indexes

```php
// Query columns that are indexed
$users = DB::table('users')
    ->where('email', $email)  // email should be indexed
    ->first();
```

### 3. Select Only Needed Columns

```php
// Good ✅
$users = DB::table('users')->select('id', 'name', 'email')->get();

// Bad ❌ - May load unnecessary data
$users = DB::table('users')->get();
```

### 4. Use Chunking for Large Datasets

```php
// Good ✅
DB::table('users')->chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process
    }
});

// Bad ❌ - May cause memory issues
$users = DB::table('users')->get();
```

### 5. Use Transactions for Multiple Writes

```php
// Good ✅
DB::transaction(function () {
    DB::table('users')->insert([...]);
    DB::table('profiles')->insert([...]);
});
```

## Next Steps

- [Database Getting Started](getting-started.md)
- [Migrations](migrations.md)
- [Schema Builder](schema-builder.md)
- [Seeders](seeders.md)
