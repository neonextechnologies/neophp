# Core Classes

Reference documentation for NeoPhP core classes.

## Application

The main application container.

### Methods

#### `instance()`

Get the application instance.

```php
$app = Application::instance();
```

#### `bind($abstract, $concrete)`

Bind a class or interface to the container.

```php
$app->bind(UserRepositoryInterface::class, UserRepository::class);

$app->bind(PaymentService::class, function($app) {
    return new PaymentService($app->make(ApiClient::class));
});
```

#### `singleton($abstract, $concrete)`

Bind a singleton to the container.

```php
$app->singleton(CacheManager::class, function($app) {
    return new CacheManager(config('cache'));
});
```

#### `make($abstract, array $parameters = [])`

Resolve a class from the container.

```php
$repository = $app->make(UserRepository::class);

$service = $app->make(PaymentService::class, [
    'apiKey' => 'key_123'
]);
```

#### `when($concrete)->needs($abstract)->give($implementation)`

Contextual binding.

```php
$app->when(ReportController::class)
    ->needs(LoggerInterface::class)
    ->give(FileLogger::class);
```

---

## Container

Dependency injection container.

### Methods

#### `get($id)`

Get an entry from the container (PSR-11).

```php
$service = $container->get(UserService::class);
```

#### `has($id)`

Check if container can return an entry (PSR-11).

```php
if ($container->has(CacheService::class)) {
    // Service is bound
}
```

#### `bound($abstract)`

Check if a binding exists.

```php
if ($container->bound(PaymentGateway::class)) {
    // Binding exists
}
```

#### `resolved($abstract)`

Check if a binding has been resolved.

```php
if ($container->resolved(DatabaseConnection::class)) {
    // Already instantiated
}
```

#### `alias($abstract, $alias)`

Create an alias for a binding.

```php
$container->alias(UserRepository::class, 'user.repository');

$repo = $container->make('user.repository');
```

#### `tag($abstracts, $tags)`

Tag bindings for bulk resolution.

```php
$container->tag([
    EmailNotification::class,
    SmsNotification::class,
    PushNotification::class
], 'notifications');

$notifications = $container->tagged('notifications');
```

---

## Config

Configuration management.

### Methods

#### `get($key, $default = null)`

Get configuration value.

```php
$dbHost = Config::get('database.host', 'localhost');
$appName = Config::get('app.name');
```

#### `set($key, $value)`

Set configuration value at runtime.

```php
Config::set('app.debug', false);
Config::set('services.stripe.key', 'sk_test_...');
```

#### `has($key)`

Check if configuration key exists.

```php
if (Config::has('services.stripe')) {
    // Stripe is configured
}
```

#### `all()`

Get all configuration values.

```php
$allConfig = Config::all();
```

---

## Request

HTTP request handling.

### Properties

```php
$request->method;      // GET, POST, PUT, DELETE, etc.
$request->url;         // Full URL
$request->path;        // Path without query string
$request->query;       // Query parameters
$request->post;        // POST data
$request->files;       // Uploaded files
$request->headers;     // Request headers
$request->cookies;     // Cookies
```

### Methods

#### `input($key, $default = null)`

Get input value from request.

```php
$name = $request->input('name');
$email = $request->input('email', 'default@example.com');
```

#### `only($keys)`

Get only specified input keys.

```php
$credentials = $request->only(['email', 'password']);
```

#### `except($keys)`

Get all input except specified keys.

```php
$data = $request->except(['_token', '_method']);
```

#### `has($key)`

Check if input key exists.

```php
if ($request->has('email')) {
    // Email parameter is present
}
```

#### `filled($key)`

Check if input key exists and is not empty.

```php
if ($request->filled('phone')) {
    // Phone number provided
}
```

#### `all()`

Get all input data.

```php
$allInput = $request->all();
```

#### `method()`

Get HTTP method.

```php
$method = $request->method(); // GET, POST, etc.
```

#### `isMethod($method)`

Check HTTP method.

```php
if ($request->isMethod('POST')) {
    // Handle POST request
}
```

#### `ajax()`

Check if request is AJAX.

```php
if ($request->ajax()) {
    return response()->json($data);
}
```

#### `header($key, $default = null)`

Get request header.

```php
$contentType = $request->header('Content-Type');
$authorization = $request->header('Authorization');
```

#### `bearerToken()`

Get bearer token from Authorization header.

```php
$token = $request->bearerToken();
```

#### `ip()`

Get client IP address.

```php
$ip = $request->ip();
```

#### `userAgent()`

Get user agent string.

```php
$userAgent = $request->userAgent();
```

#### `validate($rules, $messages = [])`

Validate request input.

```php
$validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8'
], [
    'email.required' => 'Email is required'
]);
```

#### `file($key)`

Get uploaded file.

```php
$avatar = $request->file('avatar');

if ($avatar->isValid()) {
    $path = $avatar->store('avatars');
}
```

#### `hasFile($key)`

Check if file was uploaded.

```php
if ($request->hasFile('document')) {
    // File uploaded
}
```

---

## Response

HTTP response handling.

### Methods

#### `make($content, $status = 200, $headers = [])`

Create response.

```php
return response()->make('Hello World', 200);
```

#### `json($data, $status = 200)`

Create JSON response.

```php
return response()->json([
    'success' => true,
    'data' => $users
]);
```

#### `view($view, $data = [])`

Create view response.

```php
return response()->view('users.index', ['users' => $users]);
```

#### `redirect($url, $status = 302)`

Create redirect response.

```php
return response()->redirect('/dashboard');
return response()->redirect()->route('home');
return response()->redirect()->back();
```

#### `download($file, $name = null)`

Create file download response.

```php
return response()->download(storage_path('reports/sales.pdf'));
return response()->download($pathToFile, 'report.pdf');
```

#### `stream($callback)`

Create streaming response.

```php
return response()->stream(function() {
    echo 'Chunk 1';
    flush();
    sleep(1);
    echo 'Chunk 2';
});
```

#### `header($key, $value)`

Add response header.

```php
return response()
    ->json($data)
    ->header('X-Custom-Header', 'value');
```

#### `withHeaders($headers)`

Add multiple headers.

```php
return response()
    ->json($data)
    ->withHeaders([
        'X-Header-One' => 'Value 1',
        'X-Header-Two' => 'Value 2'
    ]);
```

#### `cookie($name, $value, $minutes)`

Add cookie to response.

```php
return response()
    ->view('welcome')
    ->cookie('theme', 'dark', 60);
```

---

## Router

Route management and registration.

### Methods

#### `get($uri, $action)`

Register GET route.

```php
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
```

#### `post($uri, $action)`

Register POST route.

```php
Route::post('/users', [UserController::class, 'store']);
```

#### `put($uri, $action)`

Register PUT route.

```php
Route::put('/users/{id}', [UserController::class, 'update']);
```

#### `delete($uri, $action)`

Register DELETE route.

```php
Route::delete('/users/{id}', [UserController::class, 'destroy']);
```

#### `match($methods, $uri, $action)`

Register route for multiple HTTP methods.

```php
Route::match(['GET', 'POST'], '/form', [FormController::class, 'handle']);
```

#### `any($uri, $action)`

Register route for all HTTP methods.

```php
Route::any('/webhook', [WebhookController::class, 'handle']);
```

#### `group($attributes, $callback)`

Group routes with shared attributes.

```php
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function() {
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/posts', [AdminController::class, 'posts']);
});
```

#### `prefix($prefix)`

Set route prefix.

```php
Route::prefix('api')->group(function() {
    Route::get('/users', [ApiController::class, 'users']);
});
```

#### `middleware($middleware)`

Apply middleware to routes.

```php
Route::middleware('auth')->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

#### `name($name)`

Name a route.

```php
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

// Generate URL
$url = route('profile');
```

#### `where($name, $expression)`

Add parameter constraint.

```php
Route::get('/users/{id}', [UserController::class, 'show'])
    ->where('id', '[0-9]+');

Route::get('/posts/{slug}', [PostController::class, 'show'])
    ->where('slug', '[a-z-]+');
```

---

## Validator

Input validation.

### Methods

#### `make($data, $rules, $messages = [])`

Create validator instance.

```php
$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'password' => 'required|min:8'
]);

if ($validator->fails()) {
    return back()->withErrors($validator);
}
```

#### `validate($data, $rules)`

Validate and return validated data.

```php
$validated = Validator::validate($request->all(), [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users'
]);
```

#### `fails()`

Check if validation failed.

```php
if ($validator->fails()) {
    // Validation failed
}
```

#### `passes()`

Check if validation passed.

```php
if ($validator->passes()) {
    // Validation passed
}
```

#### `errors()`

Get validation errors.

```php
$errors = $validator->errors();

foreach ($errors->all() as $error) {
    echo $error;
}
```

#### `validated()`

Get validated data.

```php
$validated = $validator->validated();
```

---

## Collection

Array manipulation helper.

### Methods

#### `map($callback)`

Transform items.

```php
$names = $users->map(function($user) {
    return $user->name;
});
```

#### `filter($callback)`

Filter items.

```php
$active = $users->filter(function($user) {
    return $user->is_active;
});
```

#### `first($callback = null)`

Get first item.

```php
$first = $collection->first();

$firstActive = $users->first(function($user) {
    return $user->is_active;
});
```

#### `pluck($key)`

Extract values by key.

```php
$emails = $users->pluck('email');
$names = $users->pluck('name', 'id'); // Keyed by ID
```

#### `where($key, $value)`

Filter by key/value.

```php
$admins = $users->where('role', 'admin');
```

#### `sum($key = null)`

Sum values.

```php
$total = $orders->sum('total');
$count = $collection->sum(); // Sum all values
```

#### `chunk($size)`

Split into chunks.

```php
$chunks = $users->chunk(100);

foreach ($chunks as $chunk) {
    // Process 100 users at a time
}
```

#### `sort($callback = null)`

Sort collection.

```php
$sorted = $collection->sort();

$sorted = $users->sort(function($a, $b) {
    return $a->name <=> $b->name;
});
```

---

## Str

String manipulation helper.

### Methods

#### `slug($title)`

Generate URL-friendly slug.

```php
$slug = Str::slug('Hello World'); // hello-world
```

#### `random($length = 16)`

Generate random string.

```php
$token = Str::random(32);
```

#### `contains($haystack, $needles)`

Check if string contains substring.

```php
if (Str::contains($email, '@gmail.com')) {
    // Gmail address
}
```

#### `startsWith($haystack, $needles)`

Check if string starts with substring.

```php
if (Str::startsWith($url, 'https://')) {
    // Secure URL
}
```

#### `endsWith($haystack, $needles)`

Check if string ends with substring.

```php
if (Str::endsWith($file, '.pdf')) {
    // PDF file
}
```

#### `limit($value, $limit = 100)`

Limit string length.

```php
$excerpt = Str::limit($content, 200);
```

---

## Next Steps

- [Database Classes](database.md)
- [HTTP Classes](http.md)
- [Validation Classes](validation.md)
- [Cache Classes](cache.md)
