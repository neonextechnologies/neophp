# MVC Architecture Guide

## ตอนนี้มี MVC ครบแล้ว! ✅

### **Model** (Eloquent-like ORM)

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class User extends Model
{
    protected static $table = 'users';
    
    // Find
    $user = User::find(1);
    
    // Where queries
    $users = User::where('status', 'active')->get();
    $user = User::where('email', 'john@example.com')->first();
    
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
    
    // All records
    $users = User::all();
}
```

### **View** (Template Engine)

```php
// Controller
return response(view('users.show', [
    'user' => $user
]));
```

**Layout** (`resources/views/layouts/app.php`):
```php
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'App' ?></title>
    <?php $this->yield('styles') ?>
</head>
<body>
    <?= $content ?>
    <?php $this->yield('scripts') ?>
</body>
</html>
```

**View** (`resources/views/users/show.php`):
```php
<?php $this->layout('layouts.app'); ?>

<h1><?= $this->e($user['name']) ?></h1>
<p>Email: <?= $this->e($user['email']) ?></p>
```

### **Controller**

```php
<?php

namespace App\Controllers;

use NeoPhp\Core\Attributes\Controller;
use NeoPhp\Core\Attributes\Get;
use App\Models\User;

#[Controller(prefix: '/users')]
class UserController
{
    #[Get('/{id}')]
    public function show(Request $request, string $id): Response
    {
        $user = User::find($id);
        
        return response(view('users.show', [
            'user' => $user
        ]));
    }
}
```

## โครงสร้างเต็มรูปแบบ

```
app/
├── Models/              # ← Eloquent-like Models
│   └── User.php
├── Controllers/         # ← Controllers (MVC)
│   └── UserController.php
├── Modules/            # ← Modular Structure (Neonex style)
│   └── User/
│       ├── UserModule.php
│       ├── Controllers/
│       ├── Services/
│       └── Repositories/
└── Views/              # ← (ถ้าใช้แบบ traditional)

resources/
└── views/              # ← View Templates
    ├── layouts/
    │   └── app.php
    └── users/
        └── show.php
```

## สรุป: MVC + Modular

**แบบ Traditional MVC:**
```
Model → Controller → View
```

**แบบ Modular (Neonex style):**
```
Module → Controller → Service → Repository
```

**ใช้ทั้ง 2 แบบได้!** 🎯
