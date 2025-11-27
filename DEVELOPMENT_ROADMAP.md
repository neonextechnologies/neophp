# 🗺️ NeoPhp Full-Stack Framework - Development Roadmap

## 📊 สถานะปัจจุบัน (Current Status)

### ✅ ส่วนที่มีแล้ว (Foundation Layer - 70% Complete)

#### 🏗️ Core Infrastructure
- ✅ **Container/DI** - Dependency Injection Container
- ✅ **Service Provider** - Laravel-style service providers
- ✅ **Config System** - Configuration management
- ✅ **Router** - HTTP routing system
- ✅ **Middleware** - HTTP middleware pipeline
- ✅ **Request/Response** - HTTP abstractions
- ✅ **Plugin System** - WordPress-style hooks
- ✅ **Metadata System** - PHP 8 Attributes

#### 🗄️ Database Layer (Basic)
- ✅ **Database Connection** - PDO wrapper
- ✅ **Query Builder** - Basic SQL builder
- ✅ **Model (ORM)** - Simple ORM
- ✅ **Migration System** - Database migrations
- ✅ **Schema Builder** - Table creation
- ✅ **Multi-DB Support** - MySQL, PostgreSQL, SQLite

#### 🎨 View Layer (Basic)
- ✅ **View System** - Template rendering
- ✅ **Blade Engine** - Blade-like templating
- ⚠️ **Template Inheritance** - Basic only

#### 🔐 Authentication (Basic)
- ✅ **Auth System** - Basic session-based auth
- ✅ **JWT** - JWT token support
- ✅ **Role System** - Basic RBAC
- ⚠️ **Password Reset** - Not implemented
- ⚠️ **Email Verification** - Not implemented

#### 🛠️ CLI Tools
- ✅ **Console Application** - CLI framework
- ✅ **20+ Commands** - Generators, migrations, etc.
- ✅ **Code Generators** - Controllers, models, etc.

#### 📦 Other Components
- ✅ **Cache System** - File/Redis cache
- ✅ **Event System** - Event dispatcher
- ✅ **Queue System** - Job queue (basic)
- ✅ **Logging** - PSR-3 logger
- ✅ **Mail** - Email sending
- ✅ **Storage** - File storage
- ✅ **Session** - Session management
- ✅ **Validation** - Input validation
- ✅ **Pagination** - Data pagination
- ✅ **Security** - CSRF, XSS protection
- ✅ **Performance** - Benchmarking tools

---

## 🎯 ต้องพัฒนาเพิ่มเติม (Development Required - 30%)

### 🔴 CRITICAL - ต้องมีเพื่อเป็น Full Framework

#### 1. **ORM/Eloquent-like System** 🔴 (Priority: HIGHEST)
**ปัจจุบัน:** มี Model แต่เป็น basic มาก ยังไม่มีความสามารถเต็มรูปแบบ

**ต้องพัฒนา:**
```php
// ✅ มีแล้ว (Basic)
$users = User::all();
$user = User::find(1);
$user = User::create(['name' => 'John']);

// ❌ ยังไม่มี - ต้องพัฒนา
// Relationships
$user->posts()->get();
$post->comments()->paginate(10);
$user->roles()->attach($roleId);

// Eager Loading
$users = User::with('posts', 'comments')->get();

// Query Scopes
$activeUsers = User::active()->verified()->get();

// Accessors & Mutators
$user->full_name; // accessor
$user->password = 'secret'; // mutator (auto-hash)

// Model Events
User::creating(function($user) { ... });
User::updated(function($user) { ... });

// Soft Deletes
$user->delete(); // soft delete
User::withTrashed()->get();
User::onlyTrashed()->get();

// Mass Assignment Protection
protected $fillable = ['name', 'email'];
protected $guarded = ['password'];

// Casting
protected $casts = [
    'is_admin' => 'boolean',
    'settings' => 'array',
    'created_at' => 'datetime'
];
```

**ไฟล์ที่ต้องพัฒนา:**
- `src/Database/Model.php` - เพิ่มฟีเจอร์ทั้งหมด
- `src/Database/Relations/` - สร้างโฟลเดอร์ใหม่
  - `HasOne.php`
  - `HasMany.php`
  - `BelongsTo.php`
  - `BelongsToMany.php`
  - `MorphOne.php`
  - `MorphMany.php`
  - `MorphToMany.php`
- `src/Database/Concerns/` - Traits สำหรับ Model
  - `HasAttributes.php`
  - `HasRelationships.php`
  - `HasTimestamps.php`
  - `SoftDeletes.php`
  - `HidesAttributes.php`
- `src/Database/Eloquent/` - Core Eloquent
  - `Builder.php` - Query builder for models
  - `Collection.php` - Model collection
  - `SoftDeletingScope.php`

---

#### 2. **Advanced Authentication & Authorization** 🔴 (Priority: HIGH)

**ปัจจุบัน:** มี Auth และ Role แบบ basic

**ต้องพัฒนา:**
```php
// ❌ ยังไม่มี - Authentication Features
// Password Reset
Password::reset($email, $token, $newPassword);

// Email Verification
$user->sendEmailVerificationNotification();
$user->markEmailAsVerified();

// Remember Me
auth()->attempt($credentials, $remember = true);

// Multi-Auth Guards
auth('admin')->user();
auth('api')->check();

// Social Login (OAuth)
Socialite::driver('github')->redirect();
$user = Socialite::driver('github')->user();

// Two-Factor Authentication
$user->enableTwoFactorAuthentication();
$user->confirmTwoFactorAuthentication($code);

// ❌ Authorization (Policies & Gates)
// Gates
Gate::define('update-post', function ($user, $post) {
    return $user->id === $post->user_id;
});

if (Gate::allows('update-post', $post)) { ... }

// Policies
class PostPolicy {
    public function update(User $user, Post $post) {
        return $user->id === $post->user_id;
    }
}

// Usage in controller
$this->authorize('update', $post);

// Blade directives
@can('update', $post)
    <button>Edit</button>
@endcan
```

**ไฟล์ที่ต้องสร้าง:**
- `src/Auth/`
  - `PasswordReset.php` - Password reset functionality
  - `EmailVerification.php` - Email verification
  - `TwoFactorAuthentication.php` - 2FA support
  - `Guards/` - Auth guards
    - `SessionGuard.php`
    - `TokenGuard.php`
    - `JwtGuard.php`
- `src/Auth/Access/` - Authorization
  - `Gate.php` - Gate system
  - `Policy.php` - Base policy
  - `AuthorizesRequests.php` - Trait for controllers
- `src/Auth/Notifications/`
  - `ResetPassword.php`
  - `VerifyEmail.php`

---

#### 3. **Form Request Validation** 🟡 (Priority: MEDIUM)

**ปัจจุบัน:** มี Validator แบบ basic

**ต้องพัฒนา:**
```php
// ❌ ยังไม่มี - Form Request Classes
namespace App\Http\Requests;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'string|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'กรุณาระบุหัวข้อ',
            'body.required' => 'กรุณาระบุเนื้อหา'
        ];
    }
}

// Usage in Controller
public function store(StorePostRequest $request)
{
    // Already validated!
    $validated = $request->validated();
    Post::create($validated);
}
```

**ไฟล์ที่ต้องสร้าง:**
- `src/Http/FormRequest.php` - Base form request
- `src/Validation/ValidatesRequests.php` - Trait
- เพิ่ม validation rules ใน `src/Validation/Rules/`

---

#### 4. **API Resources & Transformers** 🟡 (Priority: MEDIUM)

**ปัจจุบัน:** ไม่มี API resource layer

**ต้องพัฒนา:**
```php
// ❌ ยังไม่มี - API Resources
namespace App\Http\Resources;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

// Usage
return UserResource::collection(User::all());
return new UserResource($user);

// With additional data
return UserResource::collection($users)
    ->additional(['meta' => ['total' => 100]]);
```

**ไฟล์ที่ต้องสร้าง:**
- `src/Http/Resources/`
  - `JsonResource.php` - Base resource
  - `ResourceCollection.php` - Collection resource
  - `ConditionallyLoadsAttributes.php` - Trait

---

#### 5. **Queue System Enhancement** 🟡 (Priority: MEDIUM)

**ปัจจุบัน:** มี Queue แบบ basic

**ต้องพัฒนาเพิ่ม:**
```php
// ✅ มีแล้ว (Basic)
Queue::push(SendEmailJob::class, $data);

// ❌ ต้องเพิ่ม
// Job Classes
class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        // Process podcast
    }

    public function failed(\Throwable $exception): void
    {
        // Handle failure
    }
}

// Dispatch
ProcessPodcast::dispatch($podcast);
ProcessPodcast::dispatch($podcast)->delay(now()->addMinutes(10));
ProcessPodcast::dispatch($podcast)->onQueue('processing');

// Chain jobs
Bus::chain([
    new ProcessPodcast($podcast),
    new PublishPodcast($podcast),
    new NotifyUsers($podcast)
])->dispatch();

// Batch jobs
Bus::batch([
    new ProcessPodcast($podcast1),
    new ProcessPodcast($podcast2),
])->then(function (Batch $batch) {
    // All jobs completed
})->dispatch();
```

**ไฟล์ที่ต้องเพิ่ม:**
- `src/Queue/`
  - `Job.php` - Base job class
  - `Dispatchable.php` - Trait
  - `InteractsWithQueue.php` - Trait
  - `Queueable.php` - Trait
  - `ShouldQueue.php` - Interface
  - `Bus.php` - Job dispatcher
  - `Chain.php` - Job chaining
  - `Batch.php` - Job batching

---

#### 6. **Event Broadcasting** 🟢 (Priority: LOW)

**ปัจจุบัน:** มี Event dispatcher แบบง่าย

**ต้องพัฒนาเพิ่ม:**
```php
// ❌ ยังไม่มี - Broadcasting
class OrderShipped implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.'.$this->order->id),
        ];
    }
}

// Listen in JavaScript (with Laravel Echo)
Echo.private(`orders.${orderId}`)
    .listen('OrderShipped', (e) => {
        console.log(e.order);
    });
```

**ไฟล์ที่ต้องสร้าง:**
- `src/Broadcasting/`
  - `BroadcastManager.php`
  - `Broadcasters/`
    - `PusherBroadcaster.php`
    - `RedisBroadcaster.php`
  - `Channel.php`
  - `PrivateChannel.php`
  - `PresenceChannel.php`

---

#### 7. **File Upload & Storage Enhancement** 🟡 (Priority: MEDIUM)

**ปัจจุบัน:** มี Storage basic

**ต้องพัฒนาเพิ่ม:**
```php
// ✅ มีแล้ว (Basic)
Storage::put('file.txt', 'contents');
Storage::get('file.txt');

// ❌ ต้องเพิ่ม
// File Upload Handling
$request->file('avatar')->store('avatars');
$request->file('avatar')->storeAs('avatars', 'filename.jpg');

// Cloud Storage (S3, Google Cloud, etc.)
Storage::disk('s3')->put('file.txt', 'contents');

// Image Processing
Image::make($request->file('photo'))
    ->resize(300, 200)
    ->save(storage_path('app/photos/thumbnail.jpg'));

// Download Response
return Storage::download('file.txt');
return Storage::download('file.txt', 'custom-name.txt');

// Temporary URLs (for private files)
$url = Storage::temporaryUrl('file.txt', now()->addMinutes(5));
```

**ไฟล์ที่ต้องเพิ่ม:**
- `src/Storage/`
  - `UploadedFile.php` - File upload handling
  - `Filesystem.php` - Enhanced filesystem
  - `Drivers/`
    - `S3Driver.php`
    - `GoogleCloudDriver.php`
- `src/Http/UploadedFile.php` - HTTP file upload

---

#### 8. **Mail Enhancement** 🟡 (Priority: MEDIUM)

**ปัจจุบัน:** มี Mail แบบ basic

**ต้องพัฒนาเพิ่ม:**
```php
// ✅ มีแล้ว (Basic)
Mail::send($to, $subject, $body);

// ❌ ต้องเพิ่ม - Mailable Classes
namespace App\Mail;

class OrderShipped extends Mailable
{
    public function build()
    {
        return $this->view('emails.orders.shipped')
                    ->with(['order' => $this->order])
                    ->attach('/path/to/file');
    }
}

// Send
Mail::to($user)->send(new OrderShipped($order));

// Queue
Mail::to($user)->queue(new OrderShipped($order));

// Markdown Emails
return $this->markdown('emails.orders.shipped');
```

**ไฟล์ที่ต้องสร้าง:**
- `src/Mail/`
  - `Mailable.php` - Base mailable
  - `Mailer.php` - Enhanced mailer
  - `Markdown/` - Markdown email support
  - `Transport/` - Mail transport drivers

---

#### 9. **Testing Support** 🟢 (Priority: LOW)

**ปัจจุบัน:** ไม่มี testing utilities

**ต้องพัฒนา:**
```php
// ❌ ต้องสร้าง - Testing Helpers
class UserTest extends TestCase
{
    public function test_user_can_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->post('/login', [
                             'email' => $user->email,
                             'password' => 'password'
                         ]);

        $response->assertStatus(200);
        $this->assertAuthenticated();
    }

    public function test_database()
    {
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }
}

// Model Factories
User::factory()->count(10)->create();
Post::factory()->for($user)->create();
```

**ไฟล์ที่ต้องสร้าง:**
- `src/Testing/`
  - `TestCase.php` - Base test case
  - `Concerns/`
    - `InteractsWithDatabase.php`
    - `MakesHttpRequests.php`
    - `InteractsWithAuthentication.php`
  - `Factories/`
    - `Factory.php` - Model factory
    - `HasFactory.php` - Trait

---

#### 10. **Localization (i18n)** 🟢 (Priority: LOW)

**ปัจจุบัน:** ไม่มี

**ต้องพัฒนา:**
```php
// ❌ ต้องสร้าง
// Translation
echo __('messages.welcome'); // สวัสดี
echo trans('messages.welcome');

// With parameters
echo __('messages.hello', ['name' => 'John']); // สวัสดี John

// Pluralization
echo trans_choice('messages.apples', 10); // 10 apples

// Blade
@lang('messages.welcome')
{{ __('messages.hello') }}

// Locales
app()->setLocale('th');
app()->getLocale(); // 'th'
```

**โครงสร้าง:**
```
resources/
└── lang/
    ├── en/
    │   ├── messages.php
    │   └── validation.php
    └── th/
        ├── messages.php
        └── validation.php
```

**ไฟล์ที่ต้องสร้าง:**
- `src/Translation/`
  - `Translator.php`
  - `FileLoader.php`
  - `MessageSelector.php`

---

### 📚 Documentation & Developer Experience

#### 11. **Better Documentation** 📖
- API Documentation (PHPDoc)
- User Guide (Getting Started, Tutorials)
- Best Practices Guide
- Architecture Guide

#### 12. **Better Error Pages** 🎨
- ปัจจุบัน: Error pages แบบ basic
- ต้อง: Whoops-style error pages (dev mode)
- Custom error pages (production)

#### 13. **Developer Toolbar** 🛠️
- Debug bar (like Laravel Debugbar)
- Query logger
- Performance profiler
- Route list viewer

---

## 🎯 Recommended Development Order

### Phase 1: Core Enhancements (2-4 weeks)
1. ✅ **ORM/Eloquent System** - ฟีเจอร์สำคัญที่สุด
2. ✅ **Form Request Validation** - ใช้งานบ่อย
3. ✅ **API Resources** - สำหรับ API development

### Phase 2: Authentication & Authorization (1-2 weeks)
4. ✅ **Advanced Auth** - Password reset, email verification
5. ✅ **Authorization** - Gates & Policies

### Phase 3: Infrastructure (2-3 weeks)
6. ✅ **Queue Enhancement** - Job classes, chains, batches
7. ✅ **Storage Enhancement** - File uploads, cloud storage
8. ✅ **Mail Enhancement** - Mailable classes

### Phase 4: Additional Features (1-2 weeks)
9. ✅ **Testing Support** - Factories, testing helpers
10. ✅ **Localization** - Multi-language support
11. ✅ **Event Broadcasting** - Real-time features

### Phase 5: Developer Experience (1 week)
12. ✅ **Better Error Pages**
13. ✅ **Developer Toolbar**
14. ✅ **Documentation**

---

## 📊 Progress Tracking

- **Current**: 70% Foundation ✅
- **Target**: 100% Full-Stack Framework 🎯
- **Estimated Time**: 8-12 weeks (full-time)
- **Estimated Effort**: ~200-300 hours

---

## 🚀 Quick Start After Development

เมื่อพัฒนาเสร็จ ผู้ใช้จะสามารถ:

```bash
# Create new project
composer create-project neonex/neophp myapp

# Generate code
php neo make:model Post -m -c -r
php neo make:request StorePostRequest
php neo make:policy PostPolicy
php neo make:resource PostResource
php neo make:mail OrderShipped

# Database
php neo migrate
php neo db:seed

# Testing
php neo test

# Serve
php neo serve
```

---

## 📝 Notes

- ควรพัฒนาทีละ Phase เพื่อให้แน่ใจว่าแต่ละส่วนทำงานได้ดี
- แต่ละ feature ควรมี tests
- Documentation ควรทำควบคู่ไปกับการพัฒนา
- ควร maintain backward compatibility

---

**Created**: November 27, 2025  
**Status**: Planning Phase  
**Next Action**: Start Phase 1 - ORM Enhancement
