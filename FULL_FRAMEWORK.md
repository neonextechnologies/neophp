# 🚀 NeoPhp Full-Stack Framework

**เวอร์ชัน 3.0** - Full-Featured Framework พร้อม Modular + MVC

## ✨ คุณสมบัติครบถ้วน

### 🏗️ Architecture
- ✅ **Modular Monolith** (Neonex/NestJS style)
- ✅ **MVC Pattern** (Model-View-Controller)
- ✅ **HMVC** (Hierarchical MVC)
- ✅ **Dependency Injection**
- ✅ **Repository Pattern**

### 💾 Database & ORM
- ✅ **Eloquent-like Model**
- ✅ **Query Builder**
- ✅ **Migrations**
- ✅ **Relationships**
- ✅ **Soft Deletes**

### 🎨 Views
- ✅ **Blade Template Engine**
- ✅ **PHP Templates**
- ✅ **Layouts & Sections**
- ✅ **View Composers**

### 🔐 Security
- ✅ **Authentication**
- ✅ **Authorization**
- ✅ **CSRF Protection**
- ✅ **Password Hashing**
- ✅ **Validation**

### 🛠️ Tools
- ✅ **CLI Generator**
- ✅ **Middleware**
- ✅ **Cache System**
- ✅ **Session Management**
- ✅ **Error Handling**

## 📦 ใช้งานทั้ง 2 แบบ

### แบบที่ 1: Traditional MVC

```
app/
├── Models/
│   └── User.php          # Eloquent Model
├── Controllers/
│   └── UserController.php
└── Views/
    └── users/
        └── index.blade.php
```

```php
// Model
$users = User::where('active', true)->get();

// Controller
class UserController {
    public function index() {
        $users = User::all();
        return view('users.index', ['users' => $users]);
    }
}

// View (Blade)
@foreach($users as $user)
    <div>{{ $user->name }}</div>
@endforeach
```

### แบบที่ 2: Modular (Neonex style)

```
app/Modules/User/
├── UserModule.php         # #[Module]
├── Controllers/
│   └── UserController.php # #[Controller]
├── Services/
│   └── UserService.php    # #[Injectable]
└── Repositories/
    └── UserRepository.php # Repository
```

```php
#[Module(
    controllers: [UserController::class],
    providers: [UserService::class]
)]
class UserModule { }

#[Controller(prefix: '/api/users')]
class UserController {
    public function __construct(
        protected UserService $service
    ) { }
    
    #[Get('/')]
    public function index() {
        return json($this->service->findAll());
    }
}
```

## 🎯 ตัวอย่างการใช้งาน

### 1. Models (Eloquent-like)

```php
class User extends Model {
    protected static $table = 'users';
    
    // CRUD
    $user = User::find(1);
    $users = User::all();
    
    // Query Builder
    $active = User::where('status', 'active')
                  ->orderBy('name')
                  ->limit(10)
                  ->get();
    
    // Create
    $user = User::create([
        'name' => 'John',
        'email' => 'john@example.com'
    ]);
    
    // Update
    $user->name = 'Jane';
    $user->save();
    
    // Delete
    $user->delete();
    
    // Relationships
    $posts = $user->posts();
}
```

### 2. Blade Templates

```blade
{{-- layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    @yield('content')
</body>
</html>

{{-- users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <h1>Users</h1>
    
    @if(count($users) > 0)
        @foreach($users as $user)
            <div>
                <h3>{{ $user->name }}</h3>
                <p>{{ $user->email }}</p>
            </div>
        @endforeach
    @else
        <p>No users found</p>
    @endif
@endsection
```

### 3. Validation

```php
$validator = validator($request->all(), [
    'name' => 'required|min:3|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|confirmed',
    'age' => 'numeric|min:18'
]);

try {
    $validated = $validator->validate();
    // Data is valid
} catch (ValidationException $e) {
    $errors = $e->errors();
}
```

### 4. Authentication

```php
// Register
$id = auth()->register([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => 'secret123'
]);

// Login
if (auth()->attempt('john@example.com', 'secret123')) {
    // Success
}

// Check auth
if (auth()->check()) {
    $user = auth()->user();
}

// Logout
auth()->logout();

// In views
@auth
    Welcome {{ auth()->user()['name'] }}
@endauth

@guest
    Please login
@endguest
```

### 5. Middleware

```php
class AuthMiddleware extends Middleware {
    public function handle(Request $request, callable $next): Response {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return $next($request);
    }
}

// Apply to routes
$router->get('/dashboard', function() {
    return view('dashboard');
})->middleware([AuthMiddleware::class]);
```

### 6. Cache

```php
// Store
cache()->put('key', 'value', 3600);

// Get
$value = cache()->get('key', 'default');

// Remember
$users = cache()->remember('users', 3600, function() {
    return User::all();
});

// Forget
cache()->forget('key');

// Helper
$value = cache('key', 'value', 3600);
```

### 7. Migrations

```php
class CreateUsersTable extends Migration {
    public function up() {
        $this->createTable('users', function($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }
    
    public function down() {
        $this->dropTable('users');
    }
}
```

### 8. CLI Generator

```bash
# Generate Module (Full structure)
php neophp generate module Product

# Generate Model
php neophp generate model Product

# Generate Controller
php neophp generate controller ProductController

# Generate Service
php neophp generate service ProductService

# Generate Repository
php neophp generate repository ProductRepository

# Generate Migration
php neophp generate migration create_products_table
```

## 📁 โครงสร้างเต็ม

```
neophp/
├── app/
│   ├── Controllers/      # MVC Controllers
│   ├── Models/          # Eloquent Models
│   ├── Middleware/      # Middleware
│   ├── Modules/         # Modular structure
│   │   └── User/
│   │       ├── UserModule.php
│   │       ├── Controllers/
│   │       ├── Services/
│   │       └── Repositories/
│   └── Providers/       # Service Providers
├── resources/
│   └── views/           # Templates
│       ├── layouts/
│       └── users/
├── src/                 # Core Framework
│   ├── Auth/
│   ├── Cache/
│   ├── Config/
│   ├── Container/
│   ├── Core/
│   ├── Database/
│   ├── Http/
│   ├── Routing/
│   ├── Validation/
│   └── View/
├── config/              # Configuration
├── routes/              # Route definitions
├── public/              # Public assets
├── storage/             # Storage
│   ├── cache/
│   └── logs/
└── database/
    └── migrations/
```

## 🎓 คู่มือเพิ่มเติม

- **MVC_GUIDE.md** - MVC Pattern
- **NEONEX_STYLE.md** - Modular Structure
- **README.md** - Quick Start

## 🔥 สรุป

Framework นี้รองรับ **ทั้ง 2 แบบ**:

1. ✅ **Traditional MVC** - Model, View, Controller แบบดั้งเดิม
2. ✅ **Modular Monolith** - Module-based แบบ Neonex/NestJS

พร้อม features ครบถ้วน:
- ORM, Blade, Validation, Auth, Middleware, Cache, Migration, CLI

**ใช้แบบไหนก็ได้ หรือใช้ผสมกันก็ได้!** 🚀
