# NeoPhp Core Framework

**เวอร์ชัน 2.0** - ตอนนี้เหมือน Neonex Core / NestJS แล้ว! 🚀

## ความแตกต่างจากเวอร์ชัน 1.0

| Feature | เวอร์ชัน 1.0 | เวอร์ชัน 2.0 | Neonex/NestJS |
|---------|-------------|-------------|---------------|
| Module System | ❌ | ✅ PHP 8 Attributes | ✅ Decorators |
| DI Container | ✅ | ✅ Enhanced | ✅ |
| Auto-discovery | ❌ | ✅ | ✅ |
| Repository Pattern | ❌ | ✅ | ✅ TypeORM |
| CLI Generator | ❌ | ✅ `php neophp` | ✅ `nest generate` |
| Modular Monolith | ⚠️ Basic | ✅ Complete | ✅ |

## โครงสร้างแบบ Modular Monolith

```
neophp/
├── app/
│   ├── Modules/              # ← Modules แยกตาม domain (เหมือน NestJS)
│   │   ├── User/
│   │   │   ├── UserModule.php        # #[Module] decorator
│   │   │   ├── Controllers/
│   │   │   │   └── UserController.php  # #[Controller] + #[Get]/[Post]
│   │   │   ├── Services/
│   │   │   │   └── UserService.php     # #[Injectable]
│   │   │   └── Repositories/
│   │   │       └── UserRepository.php  # Repository Pattern
│   │   ├── Product/
│   │   └── Order/
│   └── AppModule.php         # Root module (imports ทุก module)
├── src/                      # Core framework
│   ├── Core/
│   │   ├── Attributes/       # PHP 8 Attributes
│   │   │   ├── Module.php
│   │   │   ├── Controller.php
│   │   │   ├── Injectable.php
│   │   │   ├── Get.php
│   │   │   └── Post.php
│   │   ├── ModuleLoader.php  # Auto-discovery
│   │   └── Application.php
│   ├── Database/
│   │   ├── Database.php
│   │   └── Repository.php
│   └── Console/
│       └── GeneratorCommand.php
├── neophp                    # CLI tool
└── composer.json
```

## ✨ คุณสมบัติใหม่ที่เหมือน Neonex Core

### 1. Module System with PHP 8 Attributes

เหมือน `@Module()` ใน NestJS:

```php
<?php

namespace App\Modules\User;

use NeoPhp\Core\Attributes\Module;

#[Module(
    controllers: [UserController::class],
    providers: [UserService::class, UserRepository::class],
    imports: [DatabaseModule::class],
    exports: [UserService::class]
)]
class UserModule
{
    //
}
```

### 2. Controller Decorators

เหมือน `@Controller()`, `@Get()`, `@Post()`:

```php
<?php

use NeoPhp\Core\Attributes\Controller;
use NeoPhp\Core\Attributes\Get;
use NeoPhp\Core\Attributes\Post;

#[Controller(prefix: '/api/users')]
class UserController
{
    public function __construct(
        protected UserService $service  // ← Auto DI
    ) {
    }

    #[Get('/')]
    public function index(Request $request): Response
    {
        return response()->json($this->service->findAll());
    }

    #[Get('/{id}')]
    public function show(Request $request, string $id): Response
    {
        return response()->json($this->service->findById($id));
    }

    #[Post('/')]
    public function create(Request $request): Response
    {
        $id = $this->service->create($request->all());
        return response()->json(['id' => $id], 201);
    }
}
```

### 3. Injectable Services

เหมือน `@Injectable()`:

```php
<?php

use NeoPhp\Core\Attributes\Injectable;

#[Injectable]
class UserService
{
    public function __construct(
        protected UserRepository $repository  // ← Auto DI
    ) {
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
```

### 4. Repository Pattern

เหมือน TypeORM repositories:

```php
<?php

use NeoPhp\Database\Repository;
use NeoPhp\Core\Attributes\Injectable;

#[Injectable]
class UserRepository extends Repository
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    // Built-in methods
    public function find(int $id): ?array { }
    public function findAll(): array { }
    public function create(array $data): int { }
    public function update(int $id, array $data): int { }
    public function delete(int $id): int { }
    
    // Custom methods
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }
}
```

### 5. Auto-discovery Modules

โหลด modules อัตโนมัติเหมือน Neonex:

```php
// public/index.php
$moduleLoader = $app->make('moduleLoader');

// Auto-discover ทุก module ใน app/Modules
$moduleLoader->loadModulesFromDirectory(
    $app->basePath('app/Modules'),
    'App\\Modules'
);
```

### 6. CLI Generator

เหมือน `nest generate`:

```bash
# Generate module พร้อม controller, service, repository
php neophp generate module Product

# Generate controller
php neophp generate controller ProductController

# Generate service
php neophp generate service ProductService

# Generate repository
php neophp generate repository ProductRepository
```

## 🚀 Quick Start

### 1. ติดตั้ง

```bash
composer install
copy .env.example .env
```

### 2. ตั้งค่า Database

แก้ไขไฟล์ `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neophp
DB_USERNAME=root
DB_PASSWORD=
```

Import schema:

```bash
mysql -u root -p neophp < database/schema.sql
```

### 3. เริ่ม Server

```bash
php -S localhost:8000 -t public
```

### 4. ทดสอบ API

```bash
# Get all users
curl http://localhost:8000/api/users

# Get user by ID
curl http://localhost:8000/api/users/1

# Create user
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"New User","email":"new@example.com"}'
```

## 📦 สร้าง Module ใหม่

### วิธีที่ 1: ใช้ CLI Generator (แนะนำ)

```bash
php neophp generate module Product
```

จะสร้าง:
```
app/Modules/Product/
├── ProductModule.php
├── Controllers/
│   └── ProductController.php
├── Services/
│   └── ProductService.php
└── Repositories/
    └── ProductRepository.php
```

### วิธีที่ 2: สร้างเอง

**1. สร้าง Module:**

```php
<?php

namespace App\Modules\Product;

use NeoPhp\Core\Attributes\Module;

#[Module(
    controllers: [ProductController::class],
    providers: [ProductService::class]
)]
class ProductModule
{
}
```

**2. สร้าง Controller:**

```php
<?php

namespace App\Modules\Product\Controllers;

use NeoPhp\Core\Attributes\Controller;
use NeoPhp\Core\Attributes\Get;

#[Controller(prefix: '/api/products')]
class ProductController
{
    public function __construct(
        protected ProductService $service
    ) {
    }

    #[Get('/')]
    public function index(Request $request): Response
    {
        return response()->json($this->service->findAll());
    }
}
```

**3. Module จะถูก auto-discover อัตโนมัติ!**

## 🔄 เปรียบเทียบกับ Neonex Core / NestJS

### Neonex Core (Go)

```go
@Module({
    controllers: [UserController],
    providers: [UserService],
})
type UserModule struct {}
```

### NestJS (TypeScript)

```typescript
@Module({
    controllers: [UserController],
    providers: [UserService],
})
export class UserModule {}
```

### NeoPhp (PHP)

```php
#[Module(
    controllers: [UserController::class],
    providers: [UserService::class]
)]
class UserModule
{
}
```

**เหมือนกัน 100%! 🎯**

## 📊 Architecture Pattern

```
┌─────────────────────────────────────────┐
│           AppModule (Root)              │
│  #[Module(imports: [User, Product])]   │
└────────────┬────────────────────────────┘
             │
        ┌────┴────┐
        │         │
   ┌────▼───┐ ┌──▼──────┐
   │  User  │ │ Product │
   │ Module │ │ Module  │
   └────┬───┘ └──┬──────┘
        │        │
   ┌────▼───┐ ┌─▼───────┐
   │Control │ │ Control │
   │  ler   │ │  ler    │
   └────┬───┘ └─┬───────┘
        │       │
   ┌────▼───┐ ┌▼────────┐
   │Service │ │ Service │
   └────┬───┘ └─┬───────┘
        │       │
   ┌────▼───┐ ┌▼────────┐
   │  Repo  │ │  Repo   │
   └────────┘ └─────────┘
```

## 🎓 Best Practices

### 1. Module Organization

- 1 Module = 1 Domain/Feature
- แยก Module ตาม Business Domain
- ใช้ Module imports สำหรับ dependencies

### 2. Dependency Injection

- ใช้ Constructor Injection
- ใช้ `#[Injectable]` สำหรับทุก Service
- ใช้ Interface สำหรับ loose coupling

### 3. Repository Pattern

- ใช้ Repository สำหรับ Data Access
- แยก Business Logic ไว้ใน Service
- ใช้ Transaction สำหรับ Complex Operations

### 4. Controller Design

- Controller = Routing + Validation เท่านั้น
- Business Logic ไปอยู่ใน Service
- ใช้ DTO สำหรับ Data Transfer

## 🔧 Advanced Features

### Custom Dependency Injection

```php
// Inject with custom token
#[Injectable]
class MyService
{
    public function __construct(
        #[Inject('custom.config')] 
        protected array $config
    ) {
    }
}
```

### Transaction Support

```php
$db = app('db');

$db->beginTransaction();

try {
    $userId = $userRepo->create($userData);
    $profileRepo->create(['user_id' => $userId]);
    
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    throw $e;
}
```

### Pagination

```php
$users = $userRepository->paginate($page, $perPage);

// Returns:
[
    'data' => [...],
    'current_page' => 1,
    'per_page' => 15,
    'total' => 100,
    'last_page' => 7,
]
```

## 📝 สรุปการเปรียบเทียบ

| Feature | Neonex Core | NestJS | NeoPhp 2.0 |
|---------|------------|---------|------------|
| Module System | ✅ @Module | ✅ @Module | ✅ #[Module] |
| DI Container | ✅ | ✅ | ✅ |
| Auto-discovery | ✅ | ✅ | ✅ |
| Repository | ✅ | ✅ TypeORM | ✅ |
| CLI Generator | ✅ | ✅ nest | ✅ php neophp |
| Decorators | ✅ | ✅ | ✅ Attributes |
| Modular Monolith | ✅ | ✅ | ✅ |

**ตอนนี้ NeoPhp เหมือน Neonex Core / NestJS แล้ว! 🎉**

---

**NeoPhp Core Framework 2.0** - A Modern PHP Framework inspired by Neonex Core & NestJS 🚀
