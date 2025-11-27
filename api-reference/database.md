# Database Classes

Complete reference for database-related classes.

## DB

Database query builder and connection manager.

### Methods

#### `connection($name = null)`

Get database connection.

```php
$connection = DB::connection('mysql');
$pgsql = DB::connection('pgsql');
```

#### `table($table)`

Begin fluent query on table.

```php
$users = DB::table('users')->get();
```

#### `select($query, $bindings = [])`

Run SELECT query.

```php
$users = DB::select('SELECT * FROM users WHERE active = ?', [1]);

$posts = DB::select('SELECT * FROM posts WHERE user_id = :userId', [
    'userId' => 123
]);
```

#### `insert($query, $bindings = [])`

Run INSERT query.

```php
DB::insert('INSERT INTO users (name, email) VALUES (?, ?)', [
    'John Doe',
    'john@example.com'
]);
```

#### `update($query, $bindings = [])`

Run UPDATE query.

```php
$affected = DB::update('UPDATE users SET active = ? WHERE id = ?', [1, 123]);
```

#### `delete($query, $bindings = [])`

Run DELETE query.

```php
$deleted = DB::delete('DELETE FROM users WHERE id = ?', [123]);
```

#### `statement($query, $bindings = [])`

Run arbitrary SQL statement.

```php
DB::statement('DROP TABLE IF EXISTS temp_table');
```

#### `transaction($callback)`

Execute within database transaction.

```php
DB::transaction(function() {
    DB::table('users')->insert(['name' => 'John']);
    DB::table('profiles')->insert(['user_id' => 1]);
});
```

#### `beginTransaction()`

Start transaction.

```php
DB::beginTransaction();

try {
    // Database operations
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

#### `commit()`

Commit transaction.

```php
DB::commit();
```

#### `rollBack()`

Rollback transaction.

```php
DB::rollBack();
```

#### `listen($callback)`

Listen to query events.

```php
DB::listen(function($query) {
    Log::info('Query executed', [
        'sql' => $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->time
    ]);
});
```

---

## QueryBuilder

Fluent query builder interface.

### Selection Methods

#### `select($columns)`

Select columns.

```php
$users = DB::table('users')
    ->select('id', 'name', 'email')
    ->get();

$users = DB::table('users')
    ->select(['id', 'name as username'])
    ->get();
```

#### `selectRaw($expression, $bindings = [])`

Raw select expression.

```php
$users = DB::table('users')
    ->selectRaw('COUNT(*) as total')
    ->first();
```

#### `distinct()`

Select distinct rows.

```php
$cities = DB::table('users')
    ->distinct()
    ->select('city')
    ->get();
```

### WHERE Clauses

#### `where($column, $operator, $value)`

Basic WHERE clause.

```php
$users = DB::table('users')
    ->where('active', '=', 1)
    ->get();

$users = DB::table('users')
    ->where('age', '>', 18)
    ->get();

// Shorthand
$users = DB::table('users')
    ->where('active', 1)
    ->get();
```

#### `orWhere($column, $operator, $value)`

OR WHERE clause.

```php
$users = DB::table('users')
    ->where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();
```

#### `whereBetween($column, $values)`

WHERE BETWEEN clause.

```php
$users = DB::table('users')
    ->whereBetween('age', [18, 65])
    ->get();
```

#### `whereIn($column, $values)`

WHERE IN clause.

```php
$users = DB::table('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->get();
```

#### `whereNull($column)`

WHERE NULL clause.

```php
$users = DB::table('users')
    ->whereNull('deleted_at')
    ->get();
```

#### `whereNotNull($column)`

WHERE NOT NULL clause.

```php
$users = DB::table('users')
    ->whereNotNull('email_verified_at')
    ->get();
```

#### `whereDate($column, $operator, $value)`

WHERE date clause.

```php
$posts = DB::table('posts')
    ->whereDate('created_at', '2024-01-01')
    ->get();
```

#### `whereRaw($sql, $bindings = [])`

Raw WHERE clause.

```php
$users = DB::table('users')
    ->whereRaw('age > ? AND status = ?', [18, 'active'])
    ->get();
```

### JOIN Clauses

#### `join($table, $first, $operator, $second)`

INNER JOIN.

```php
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', 'posts.title')
    ->get();
```

#### `leftJoin($table, $first, $operator, $second)`

LEFT JOIN.

```php
$users = DB::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->get();
```

#### `rightJoin($table, $first, $operator, $second)`

RIGHT JOIN.

```php
$posts = DB::table('posts')
    ->rightJoin('users', 'posts.user_id', '=', 'users.id')
    ->get();
```

#### `crossJoin($table)`

CROSS JOIN.

```php
$combinations = DB::table('colors')
    ->crossJoin('sizes')
    ->get();
```

### Ordering & Grouping

#### `orderBy($column, $direction = 'asc')`

Order results.

```php
$users = DB::table('users')
    ->orderBy('name', 'asc')
    ->get();

$users = DB::table('users')
    ->orderBy('created_at', 'desc')
    ->get();
```

#### `latest($column = 'created_at')`

Order by descending.

```php
$posts = DB::table('posts')
    ->latest()
    ->get();
```

#### `oldest($column = 'created_at')`

Order by ascending.

```php
$posts = DB::table('posts')
    ->oldest()
    ->get();
```

#### `groupBy($columns)`

Group results.

```php
$stats = DB::table('orders')
    ->select('user_id', DB::raw('COUNT(*) as total'))
    ->groupBy('user_id')
    ->get();
```

#### `having($column, $operator, $value)`

HAVING clause.

```php
$users = DB::table('orders')
    ->select('user_id', DB::raw('COUNT(*) as order_count'))
    ->groupBy('user_id')
    ->having('order_count', '>', 5)
    ->get();
```

### Limit & Offset

#### `limit($value)`

Limit results.

```php
$users = DB::table('users')
    ->limit(10)
    ->get();
```

#### `offset($value)`

Offset results.

```php
$users = DB::table('users')
    ->offset(20)
    ->limit(10)
    ->get();
```

#### `skip($value)`

Alias for offset.

```php
$users = DB::table('users')
    ->skip(20)
    ->take(10)
    ->get();
```

#### `take($value)`

Alias for limit.

```php
$users = DB::table('users')
    ->take(10)
    ->get();
```

### Retrieval Methods

#### `get()`

Get all results.

```php
$users = DB::table('users')->get();
```

#### `first()`

Get first result.

```php
$user = DB::table('users')
    ->where('email', 'john@example.com')
    ->first();
```

#### `find($id)`

Find by primary key.

```php
$user = DB::table('users')->find(123);
```

#### `value($column)`

Get single column value.

```php
$email = DB::table('users')
    ->where('id', 123)
    ->value('email');
```

#### `pluck($column, $key = null)`

Get array of column values.

```php
$emails = DB::table('users')->pluck('email');

$emailsById = DB::table('users')->pluck('email', 'id');
```

#### `count()`

Count results.

```php
$total = DB::table('users')->count();

$active = DB::table('users')
    ->where('active', 1)
    ->count();
```

#### `exists()`

Check if records exist.

```php
if (DB::table('users')->where('email', $email)->exists()) {
    // Email already exists
}
```

#### `doesntExist()`

Check if records don't exist.

```php
if (DB::table('users')->where('email', $email)->doesntExist()) {
    // Email is available
}
```

### Aggregates

#### `max($column)`

Get maximum value.

```php
$maxPrice = DB::table('products')->max('price');
```

#### `min($column)`

Get minimum value.

```php
$minPrice = DB::table('products')->min('price');
```

#### `avg($column)`

Get average value.

```php
$avgPrice = DB::table('products')->avg('price');
```

#### `sum($column)`

Get sum of values.

```php
$total = DB::table('orders')->sum('total');
```

### Insert Methods

#### `insert($values)`

Insert records.

```php
DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

DB::table('users')->insert([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com']
]);
```

#### `insertGetId($values)`

Insert and get ID.

```php
$id = DB::table('users')->insertGetId([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Update Methods

#### `update($values)`

Update records.

```php
$affected = DB::table('users')
    ->where('id', 123)
    ->update(['name' => 'Jane Doe']);
```

#### `increment($column, $amount = 1)`

Increment column value.

```php
DB::table('posts')
    ->where('id', 1)
    ->increment('views');

DB::table('posts')
    ->where('id', 1)
    ->increment('views', 5);
```

#### `decrement($column, $amount = 1)`

Decrement column value.

```php
DB::table('products')
    ->where('id', 1)
    ->decrement('stock');
```

### Delete Methods

#### `delete()`

Delete records.

```php
$deleted = DB::table('users')
    ->where('id', 123)
    ->delete();
```

#### `truncate()`

Truncate table.

```php
DB::table('temp_data')->truncate();
```

---

## Model

Eloquent ORM base model.

### Properties

```php
protected $table = 'users';              // Table name
protected $primaryKey = 'id';            // Primary key
protected $fillable = [];                // Mass assignable attributes
protected $guarded = [];                 // Guarded attributes
protected $hidden = [];                  // Hidden from arrays/JSON
protected $visible = [];                 // Visible in arrays/JSON
protected $casts = [];                   // Attribute casting
protected $dates = [];                   // Date attributes
protected $with = [];                    // Eager load relations
protected $timestamps = true;            // Use timestamps
```

### Query Methods

#### `all()`

Get all records.

```php
$users = User::all();
```

#### `find($id)`

Find by primary key.

```php
$user = User::find(123);
$users = User::find([1, 2, 3]);
```

#### `findOrFail($id)`

Find or throw exception.

```php
$user = User::findOrFail(123);
```

#### `first()`

Get first record.

```php
$user = User::where('email', $email)->first();
```

#### `firstOrFail()`

Get first or throw exception.

```php
$user = User::where('email', $email)->firstOrFail();
```

#### `firstOrCreate($attributes, $values = [])`

Find or create record.

```php
$user = User::firstOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe']
);
```

#### `updateOrCreate($attributes, $values = [])`

Update or create record.

```php
$user = User::updateOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe', 'active' => 1]
);
```

#### `create($attributes)`

Create new record.

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

#### `save()`

Save model instance.

```php
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();
```

#### `update($attributes)`

Update model instance.

```php
$user->update(['name' => 'Jane Doe']);
```

#### `delete()`

Delete model instance.

```php
$user->delete();
```

#### `destroy($ids)`

Delete by primary key(s).

```php
User::destroy(123);
User::destroy([1, 2, 3]);
```

### Relationships

#### `hasOne($related, $foreignKey, $localKey)`

Define one-to-one relationship.

```php
public function profile()
{
    return $this->hasOne(Profile::class);
}
```

#### `hasMany($related, $foreignKey, $localKey)`

Define one-to-many relationship.

```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

#### `belongsTo($related, $foreignKey, $ownerKey)`

Define inverse one-to-many relationship.

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

#### `belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey)`

Define many-to-many relationship.

```php
public function roles()
{
    return $this->belongsToMany(Role::class);
}
```

---

## Schema

Database schema builder.

### Methods

#### `create($table, $callback)`

Create table.

```php
Schema::create('users', function($table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});
```

#### `table($table, $callback)`

Modify existing table.

```php
Schema::table('users', function($table) {
    $table->string('phone')->nullable();
});
```

#### `drop($table)`

Drop table.

```php
Schema::drop('users');
```

#### `dropIfExists($table)`

Drop table if exists.

```php
Schema::dropIfExists('users');
```

#### `rename($from, $to)`

Rename table.

```php
Schema::rename('users', 'customers');
```

#### `hasTable($table)`

Check if table exists.

```php
if (Schema::hasTable('users')) {
    // Table exists
}
```

#### `hasColumn($table, $column)`

Check if column exists.

```php
if (Schema::hasColumn('users', 'email')) {
    // Column exists
}
```

### Column Types

```php
$table->id();                           // Auto-increment ID
$table->bigInteger('votes');            // BIGINT
$table->boolean('confirmed');           // BOOLEAN
$table->date('created_date');           // DATE
$table->dateTime('created_at');         // DATETIME
$table->decimal('amount', 8, 2);        // DECIMAL
$table->double('amount', 8, 2);         // DOUBLE
$table->enum('status', ['active']);     // ENUM
$table->float('amount', 8, 2);          // FLOAT
$table->integer('votes');               // INTEGER
$table->json('options');                // JSON
$table->longText('description');        // LONGTEXT
$table->string('name', 100);            // VARCHAR
$table->text('description');            // TEXT
$table->time('sunrise');                // TIME
$table->timestamp('added_on');          // TIMESTAMP
$table->timestamps();                   // created_at, updated_at
$table->softDeletes();                  // deleted_at
```

### Column Modifiers

```php
$table->string('email')->nullable();
$table->string('name')->default('Guest');
$table->integer('votes')->unsigned();
$table->string('email')->unique();
$table->integer('order')->autoIncrement();
$table->timestamp('created_at')->useCurrent();
$table->string('name')->comment('User name');
$table->string('name')->after('id');
$table->string('name')->first();
```

### Indexes

```php
$table->primary('id');
$table->unique('email');
$table->index('user_id');
$table->index(['user_id', 'status']);
$table->fulltext('content');

// Named indexes
$table->index('email', 'users_email_index');
```

### Foreign Keys

```php
$table->foreignId('user_id')->constrained();

$table->foreignId('user_id')
    ->constrained()
    ->onDelete('cascade')
    ->onUpdate('cascade');

$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('cascade');
```

---

## Migration

Database migration base class.

### Methods

#### `up()`

Run migration.

```php
public function up(): void
{
    Schema::create('users', function($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
}
```

#### `down()`

Rollback migration.

```php
public function down(): void
{
    Schema::dropIfExists('users');
}
```

---

## Next Steps

- [HTTP Classes](http.md)
- [Validation Classes](validation.md)
- [Cache Classes](cache.md)
- [Queue Classes](queue.md)
