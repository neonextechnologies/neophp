# Security

Protect your application with built-in security features.

## Authentication

### User Authentication

```php
<?php

namespace App\Controllers;

use NeoPhp\Http\Request;
use NeoPhp\Auth\Facades\Auth;

class AuthController
{
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            return redirect('/dashboard');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }
    
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
```

### Multi-Factor Authentication

```php
<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    private Google2FA $google2fa;
    
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }
    
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }
    
    public function getQRCode(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }
    
    public function verify(User $user, string $code): bool
    {
        return $this->google2fa->verifyKey($user->two_factor_secret, $code);
    }
    
    public function enable(User $user, string $secret): void
    {
        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $this->generateRecoveryCodes()
        ]);
    }
    
    private function generateRecoveryCodes(): array
    {
        $codes = [];
        
        for ($i = 0; $i < 8; $i++) {
            $codes[] = bin2hex(random_bytes(4));
        }
        
        return $codes;
    }
}
```

## Authorization

### Gates

```php
<?php

namespace App\Providers;

use NeoPhp\Auth\Facades\Gate;

class AuthServiceProvider
{
    public function boot(): void
    {
        Gate::define('update-post', function($user, $post) {
            return $user->id === $post->user_id;
        });
        
        Gate::define('delete-post', function($user, $post) {
            return $user->id === $post->user_id || $user->isAdmin();
        });
        
        Gate::before(function($user) {
            if ($user->isSuperAdmin()) {
                return true; // Super admin can do anything
            }
        });
    }
}

// Usage
if (Gate::allows('update-post', $post)) {
    // User can update the post
}

if (Gate::denies('delete-post', $post)) {
    abort(403);
}
```

### Policies

```php
<?php

namespace App\Policies;

use App\Models\{User, Post};

class PostPolicy
{
    public function view(?User $user, Post $post): bool
    {
        return $post->published || ($user && $user->id === $post->user_id);
    }
    
    public function create(User $user): bool
    {
        return $user->isVerified();
    }
    
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
    
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }
}

// Register in AuthServiceProvider
protected array $policies = [
    Post::class => PostPolicy::class,
];

// Usage
if ($user->can('update', $post)) {
    // User can update the post
}

$this->authorize('delete', $post);
```

### Role-Based Access Control

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }
    
    public function hasPermission(string $permission): bool
    {
        return $this->roles->flatMap->permissions
            ->contains('name', $permission);
    }
}

class Role extends Model
{
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}

class Permission extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

// Middleware
class CheckRole
{
    public function handle($request, Closure $next, string $role)
    {
        if (!auth()->user()->hasRole($role)) {
            abort(403);
        }
        
        return $next($request);
    }
}

// Usage in routes
Route::middleware('role:admin')->group(function() {
    Route::get('/admin/users', [AdminController::class, 'users']);
});
```

## CSRF Protection

### Enable CSRF Protection

```php
<?php

namespace App\Middleware;

use NeoPhp\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected array $except = [
        'api/*',
        'webhooks/*',
    ];
}
```

### CSRF in Forms

```blade
<form method="POST" action="/profile">
    @csrf
    <!-- Form fields -->
    <button type="submit">Update Profile</button>
</form>
```

### CSRF in AJAX

```javascript
// Set CSRF token globally
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

// Make request
axios.post('/api/data', {
    name: 'John'
});
```

## XSS Protection

### Escaping Output

```php
// In views
{{ $user->name }} // Automatically escaped

{!! $htmlContent !!} // Raw output (use with caution)

// Manual escaping
echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
```

### Content Security Policy

```php
<?php

namespace App\Middleware;

class AddSecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' data:;"
        );
        
        return $response;
    }
}
```

## SQL Injection Prevention

### Use Parameter Binding

```php
// Good ✅ - Parameter binding
$users = DB::table('users')
    ->where('email', $request->email)
    ->get();

// Good ✅ - Named placeholders
$users = DB::select('SELECT * FROM users WHERE email = :email', [
    'email' => $request->email
]);

// Bad ❌ - String concatenation
$users = DB::select("SELECT * FROM users WHERE email = '{$request->email}'");
```

### Query Builder Protection

```php
// Safe - query builder escapes values
User::where('status', $status)
    ->where('role', $role)
    ->get();

// Safe - parameter binding
User::whereRaw('created_at > DATE_SUB(NOW(), INTERVAL ? DAY)', [30])
    ->get();
```

## Password Security

### Password Hashing

```php
use NeoPhp\Support\Facades\Hash;

// Hash password
$hashedPassword = Hash::make($request->password);

// Verify password
if (Hash::check($request->password, $user->password)) {
    // Password is correct
}

// Check if rehash needed
if (Hash::needsRehash($user->password)) {
    $user->password = Hash::make($password);
    $user->save();
}
```

### Password Validation

```php
use NeoPhp\Validation\Rules\Password;

$request->validate([
    'password' => [
        'required',
        'confirmed',
        Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised()
    ]
]);
```

## Rate Limiting

### API Rate Limiting

```php
<?php

namespace App\Middleware;

use NeoPhp\Cache\Facades\Cache;

class ThrottleRequests
{
    public function handle($request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            abort(429, 'Too Many Requests');
        }
        
        Cache::put($key, $attempts + 1, $decayMinutes * 60);
        
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        
        return $response;
    }
    
    private function resolveRequestSignature($request): string
    {
        return sha1(
            $request->method() . '|' .
            $request->path() . '|' .
            $request->ip()
        );
    }
}

// Usage
Route::middleware('throttle:60,1')->group(function() {
    Route::get('/api/users', [UserController::class, 'index']);
});
```

### Login Throttling

```php
<?php

namespace App\Services;

use NeoPhp\Cache\Facades\Cache;

class LoginThrottler
{
    private int $maxAttempts = 5;
    private int $decayMinutes = 15;
    
    public function tooManyAttempts(string $email): bool
    {
        return Cache::get($this->throttleKey($email), 0) >= $this->maxAttempts;
    }
    
    public function incrementAttempts(string $email): void
    {
        $key = $this->throttleKey($email);
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, $this->decayMinutes * 60);
    }
    
    public function clearAttempts(string $email): void
    {
        Cache::forget($this->throttleKey($email));
    }
    
    public function availableIn(string $email): int
    {
        $key = $this->throttleKey($email);
        return Cache::ttl($key);
    }
    
    private function throttleKey(string $email): string
    {
        return 'login_attempts:' . sha1($email);
    }
}

// Usage in controller
public function login(Request $request, LoginThrottler $throttler)
{
    if ($throttler->tooManyAttempts($request->email)) {
        $seconds = $throttler->availableIn($request->email);
        
        return back()->withErrors([
            'email' => "Too many login attempts. Please try again in {$seconds} seconds."
        ]);
    }
    
    if (Auth::attempt($request->only(['email', 'password']))) {
        $throttler->clearAttempts($request->email);
        return redirect('/dashboard');
    }
    
    $throttler->incrementAttempts($request->email);
    return back()->withErrors(['email' => 'Invalid credentials']);
}
```

## Encryption

### Encrypting Data

```php
use NeoPhp\Encryption\Facades\Crypt;

// Encrypt
$encrypted = Crypt::encryptString($sensitiveData);

// Decrypt
$decrypted = Crypt::decryptString($encrypted);

// Store encrypted data
User::create([
    'name' => $request->name,
    'ssn' => Crypt::encryptString($request->ssn)
]);
```

### Model Encryption

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Encryption\Facades\Crypt;

class User extends Model
{
    protected array $encrypted = [
        'ssn',
        'credit_card',
    ];
    
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        if (in_array($key, $this->encrypted) && $value) {
            return Crypt::decryptString($value);
        }
        
        return $value;
    }
    
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encrypted) && $value) {
            $value = Crypt::encryptString($value);
        }
        
        return parent::setAttribute($key, $value);
    }
}
```

## Security Headers

```php
<?php

namespace App\Middleware;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        $response->headers->set('Strict-Transport-Security', 
            'max-age=31536000; includeSubDomains; preload'
        );
        
        return $response;
    }
}
```

## Input Validation

### Sanitizing Input

```php
<?php

namespace App\Services;

class InputSanitizer
{
    public function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    public function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    public function sanitizeHtml(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
```

### Request Validation

```php
$request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'age' => 'required|integer|min:18|max:100',
    'website' => 'nullable|url',
    'phone' => 'required|regex:/^[0-9]{10}$/',
]);
```

## File Upload Security

```php
<?php

namespace App\Services;

use NeoPhp\Http\UploadedFile;

class SecureFileUpload
{
    private array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'application/pdf',
    ];
    
    private int $maxSize = 5 * 1024 * 1024; // 5MB
    
    public function validate(UploadedFile $file): bool
    {
        if ($file->getSize() > $this->maxSize) {
            throw new \Exception('File too large');
        }
        
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \Exception('Invalid file type');
        }
        
        return true;
    }
    
    public function upload(UploadedFile $file, string $directory): string
    {
        $this->validate($file);
        
        $filename = $this->generateSafeFilename($file);
        
        $file->move(storage_path($directory), $filename);
        
        return $filename;
    }
    
    private function generateSafeFilename(UploadedFile $file): string
    {
        return md5(uniqid()) . '.' . $file->getClientOriginalExtension();
    }
}
```

## API Security

### API Token Authentication

```php
<?php

namespace App\Middleware;

use NeoPhp\Http\Request;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $apiKey = ApiKey::where('token', hash('sha256', $token))->first();
        
        if (!$apiKey || !$apiKey->isActive()) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        $apiKey->recordUsage();
        
        return $next($request);
    }
}
```

### CORS Configuration

```php
<?php

namespace App\Middleware;

class Cors
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
        
        $response = $next($request);
        
        $response->headers->set('Access-Control-Allow-Origin', '*');
        
        return $response;
    }
}
```

## Best Practices

### 1. Always Validate Input

```php
// Good ✅
$request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8'
]);

// Bad ❌
$user = User::create($request->all());
```

### 2. Use HTTPS

```php
// Redirect to HTTPS
if (!$request->secure()) {
    return redirect()->secure($request->getRequestUri());
}
```

### 3. Keep Dependencies Updated

```bash
composer update
composer audit
```

### 4. Use Environment Variables

```php
// Good ✅
$apiKey = env('API_KEY');

// Bad ❌
$apiKey = 'hardcoded-api-key';
```

### 5. Log Security Events

```php
Log::channel('security')->warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip()
]);
```

## Next Steps

- [Logging](logging.md)
- [Testing](testing.md)
- [API Authentication](../tutorials/rest-api.md)
- [Best Practices](../contributing/best-practices.md)
