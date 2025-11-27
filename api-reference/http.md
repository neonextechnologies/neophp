# HTTP Classes

Reference for HTTP-related classes.

## Request

Complete HTTP request interface.

### Properties

```php
$request->method        // HTTP method
$request->url           // Full URL
$request->path          // Path without query
$request->query         // Query parameters
$request->post          // POST data
$request->files         // Uploaded files
$request->headers       // Headers
$request->cookies       // Cookies
$request->server        // Server variables
```

### Input Methods

#### `input($key, $default = null)`

```php
$name = $request->input('name');
$email = $request->input('email', 'default@example.com');
```

#### `only($keys)`

```php
$credentials = $request->only(['email', 'password']);
```

#### `except($keys)`

```php
$data = $request->except(['_token', '_method']);
```

#### `has($key)`

```php
if ($request->has('email')) {
    // Has email parameter
}
```

#### `filled($key)`

```php
if ($request->filled('phone')) {
    // Phone is not empty
}
```

#### `missing($key)`

```php
if ($request->missing('optional_field')) {
    // Field is missing
}
```

#### `all()`

```php
$allInput = $request->all();
```

#### `get($key, $default = null)`

```php
$page = $request->get('page', 1);
```

#### `query($key, $default = null)`

```php
$search = $request->query('search');
```

#### `post($key, $default = null)`

```php
$name = $request->post('name');
```

### File Methods

#### `file($key)`

```php
$file = $request->file('avatar');
```

#### `hasFile($key)`

```php
if ($request->hasFile('document')) {
    // File uploaded
}
```

#### `allFiles()`

```php
$files = $request->allFiles();
```

### Header Methods

#### `header($key, $default = null)`

```php
$contentType = $request->header('Content-Type');
```

#### `hasHeader($key)`

```php
if ($request->hasHeader('Authorization')) {
    // Has auth header
}
```

#### `bearerToken()`

```php
$token = $request->bearerToken();
```

### Cookie Methods

#### `cookie($key, $default = null)`

```php
$theme = $request->cookie('theme', 'light');
```

#### `hasCookie($key)`

```php
if ($request->hasCookie('session_id')) {
    // Has cookie
}
```

### Method Detection

#### `method()`

```php
$method = $request->method(); // GET, POST, etc.
```

#### `isMethod($method)`

```php
if ($request->isMethod('POST')) {
    // Is POST request
}
```

#### `ajax()`

```php
if ($request->ajax()) {
    return response()->json($data);
}
```

#### `pjax()`

```php
if ($request->pjax()) {
    // PJAX request
}
```

#### `wantsJson()`

```php
if ($request->wantsJson()) {
    return response()->json($data);
}
```

#### `expectsJson()`

```php
if ($request->expectsJson()) {
    return response()->json($error, 422);
}
```

### URL Methods

#### `url()`

```php
$url = $request->url(); // Without query string
```

#### `fullUrl()`

```php
$fullUrl = $request->fullUrl(); // With query string
```

#### `path()`

```php
$path = $request->path(); // /users/123
```

#### `is($pattern)`

```php
if ($request->is('admin/*')) {
    // Admin route
}
```

#### `routeIs($name)`

```php
if ($request->routeIs('profile.edit')) {
    // On profile edit route
}
```

### Client Info

#### `ip()`

```php
$ip = $request->ip();
```

#### `userAgent()`

```php
$userAgent = $request->userAgent();
```

#### `secure()`

```php
if ($request->secure()) {
    // HTTPS request
}
```

### Authentication

#### `user()`

```php
$user = $request->user();
```

#### `setUser($user)`

```php
$request->setUser($authenticatedUser);
```

---

## Response

HTTP response builder.

### Factory Methods

#### `make($content, $status, $headers)`

```php
return response()->make('Hello World', 200);
```

#### `json($data, $status, $headers)`

```php
return response()->json([
    'success' => true,
    'data' => $users
], 200);
```

#### `view($view, $data, $status, $headers)`

```php
return response()->view('welcome', ['name' => 'John'], 200);
```

#### `download($file, $name, $headers)`

```php
return response()->download(
    storage_path('reports/sales.pdf'),
    'sales-report.pdf'
);
```

#### `file($file, $headers)`

```php
return response()->file(storage_path('documents/contract.pdf'));
```

#### `stream($callback, $status, $headers)`

```php
return response()->stream(function() {
    echo 'Chunk 1';
    flush();
    sleep(1);
    echo 'Chunk 2';
}, 200, ['Content-Type' => 'text/plain']);
```

#### `streamDownload($callback, $name, $headers)`

```php
return response()->streamDownload(function() {
    echo 'Large file content...';
}, 'export.csv');
```

### Content Methods

#### `setContent($content)`

```php
return response()
    ->setContent('Custom content')
    ->setStatusCode(200);
```

#### `getContent()`

```php
$content = $response->getContent();
```

### Status Methods

#### `setStatusCode($code, $text = null)`

```php
return response()
    ->json($data)
    ->setStatusCode(201);
```

#### `getStatusCode()`

```php
$code = $response->getStatusCode();
```

### Header Methods

#### `header($key, $value, $replace = true)`

```php
return response()
    ->json($data)
    ->header('X-Custom-Header', 'value');
```

#### `withHeaders($headers)`

```php
return response()
    ->json($data)
    ->withHeaders([
        'X-Header-One' => 'Value 1',
        'X-Header-Two' => 'Value 2'
    ]);
```

#### `getHeaders()`

```php
$headers = $response->getHeaders();
```

### Cookie Methods

#### `cookie($name, $value, $minutes, $path, $domain, $secure, $httpOnly)`

```php
return response()
    ->view('welcome')
    ->cookie('theme', 'dark', 60);
```

#### `withCookie($cookie)`

```php
$cookie = cookie('name', 'value', 60);

return response()
    ->view('welcome')
    ->withCookie($cookie);
```

### Redirect Methods

#### `redirect($url, $status, $headers)`

```php
return response()->redirect('/dashboard');
```

#### `redirectTo($url)`

```php
return response()->redirectTo('/home');
```

#### `redirectToRoute($name, $parameters)`

```php
return response()->redirectToRoute('profile.show', ['id' => 123]);
```

#### `redirectToAction($action, $parameters)`

```php
return response()->redirectToAction([UserController::class, 'show'], ['id' => 123]);
```

#### `back($status, $headers)`

```php
return response()->back();
```

#### `refresh()`

```php
return response()->refresh();
```

#### `away($url)`

```php
return response()->away('https://external-site.com');
```

#### `with($key, $value)`

```php
return redirect('/dashboard')->with('success', 'Profile updated!');
```

#### `withInput($input)`

```php
return back()->withInput();
```

#### `withErrors($errors)`

```php
return back()->withErrors($validator);
```

---

## UploadedFile

Uploaded file handling.

### Methods

#### `isValid()`

```php
if ($file->isValid()) {
    // File uploaded successfully
}
```

#### `getClientOriginalName()`

```php
$name = $file->getClientOriginalName();
```

#### `getClientOriginalExtension()`

```php
$extension = $file->getClientOriginalExtension();
```

#### `getSize()`

```php
$size = $file->getSize(); // In bytes
```

#### `getMimeType()`

```php
$mime = $file->getMimeType();
```

#### `store($path, $disk = null)`

```php
$path = $file->store('uploads');
$path = $file->store('avatars', 'public');
```

#### `storeAs($path, $name, $disk = null)`

```php
$path = $file->storeAs('uploads', 'document.pdf');
```

#### `move($directory, $name = null)`

```php
$file->move(storage_path('uploads'), 'file.pdf');
```

#### `getContent()`

```php
$content = $file->getContent();
```

#### `hashName()`

```php
$hashedName = $file->hashName();
```

---

## Middleware

HTTP middleware interface.

### Example Implementation

```php
<?php

namespace App\Middleware;

use Closure;

class ExampleMiddleware
{
    public function handle($request, Closure $next)
    {
        // Before request
        
        $response = $next($request);
        
        // After request
        
        return $response;
    }
}
```

### Terminating Middleware

```php
public function terminate($request, $response)
{
    // Cleanup after response sent
}
```

---

## Session

Session management.

### Methods

#### `get($key, $default = null)`

```php
$user = session()->get('user');
$value = session()->get('key', 'default');
```

#### `put($key, $value)`

```php
session()->put('user_id', 123);
session()->put(['key1' => 'value1', 'key2' => 'value2']);
```

#### `push($key, $value)`

```php
session()->push('notifications', 'New message');
```

#### `has($key)`

```php
if (session()->has('user_id')) {
    // Session key exists
}
```

#### `exists($key)`

```php
if (session()->exists('key')) {
    // Key exists even if null
}
```

#### `missing($key)`

```php
if (session()->missing('optional')) {
    // Key doesn't exist
}
```

#### `all()`

```php
$data = session()->all();
```

#### `forget($keys)`

```php
session()->forget('user_id');
session()->forget(['key1', 'key2']);
```

#### `flush()`

```php
session()->flush(); // Clear all session data
```

#### `regenerate()`

```php
session()->regenerate(); // Regenerate session ID
```

#### `flash($key, $value)`

```php
session()->flash('success', 'Item saved!');
```

#### `reflash()`

```php
session()->reflash(); // Keep all flash data
```

#### `keep($keys)`

```php
session()->keep(['success', 'error']);
```

---

## Cookie

Cookie management.

### Methods

#### `make($name, $value, $minutes, $path, $domain, $secure, $httpOnly)`

```php
$cookie = cookie('name', 'value', 60);

$cookie = cookie('theme', 'dark', 60, '/', '.example.com', true, true);
```

#### `forever($name, $value, $path, $domain, $secure, $httpOnly)`

```php
$cookie = cookie()->forever('remember_token', $token);
```

#### `forget($name)`

```php
$cookie = cookie()->forget('name');

return response()->view('welcome')->withCookie($cookie);
```

#### `queued()`

```php
$queuedCookies = cookie()->queued();
```

---

## JsonResponse

JSON response helper.

### Methods

#### `setData($data)`

```php
return response()
    ->json([])
    ->setData(['updated' => true]);
```

#### `getData()`

```php
$data = $response->getData();
```

#### `setEncodingOptions($options)`

```php
return response()
    ->json($data)
    ->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

---

## RedirectResponse

Redirect response helper.

### Methods

#### `with($key, $value)`

```php
return redirect('/dashboard')
    ->with('success', 'Profile updated successfully!');
```

#### `withInput($input)`

```php
return back()->withInput($request->except('password'));
```

#### `withErrors($provider, $key = 'default')`

```php
return back()->withErrors($validator, 'login');
```

#### `withFragment($fragment)`

```php
return redirect('/page')->withFragment('section-2');
```

#### `withQueryString()`

```php
return back()->withQueryString();
```

---

## Next Steps

- [Validation Classes](validation.md)
- [Cache Classes](cache.md)
- [Auth Classes](auth.md)
- [Mail Classes](mail.md)
