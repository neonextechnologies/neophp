# NeoPhp Foundation Framework

## 🎉 ตอนนี้ NeoPhp เป็น Foundation Framework แบบ Neonex Core แล้ว!

### ✅ ระบบที่เพิ่มเข้ามา (Neonex Core Style)

## 1. 📋 Contracts Layer (Pure Interfaces)

ระบบ Contract/Interface แบบ pure abstraction เหมือน Neonex Core:

```php
// src/Contracts/
- DatabaseInterface
- CacheInterface  
- QueueInterface
- LoggerInterface
- StorageInterface
- MailerInterface
- ValidatorInterface
- ServiceProviderInterface
- PluginInterface
- MetadataInterface
```

**ใช้งาน:**
```php
use NeoPhp\Contracts\DatabaseInterface;
use NeoPhp\Contracts\CacheInterface;

class UserService {
    public function __construct(
        private DatabaseInterface $db,
        private CacheInterface $cache
    ) {}
}
```

---

## 2. 🏗️ Service Provider System

ระบบ Service Provider แบบ Laravel + Neonex Core:

**สร้าง Service Provider:**
```php
use NeoPhp\Foundation\ServiceProvider;

class MyServiceProvider extends ServiceProvider
{
    protected array $provides = ['myservice'];
    protected bool $defer = true; // Deferred loading
    protected array $dependencies = [DatabaseServiceProvider::class];

    public function register(): void
    {
        $this->singleton('myservice', function($app) {
            return new MyService($app->make('db'));
        });
    }

    public function boot(): void
    {
        // Bootstrap logic after all providers registered
    }
}
```

**Provider Manager:**
```php
$providerManager = new ProviderManager($container);

// Register providers
$providerManager->registerProviders([
    DatabaseServiceProvider::class,
    CacheServiceProvider::class,
    QueueServiceProvider::class,
    MyServiceProvider::class
]);

// Boot all providers
$providerManager->bootProviders();

// Auto-discover providers
$providers = $providerManager->discover('app/Providers');
```

---

## 3. 🔌 Plugin Architecture

ระบบ Plugin แบบ WordPress + Neonex Core:

**สร้าง Plugin:**
```php
// plugins/MyPlugin/Plugin.php
namespace Plugins\MyPlugin;

use NeoPhp\Plugin\Plugin;

class MyPlugin extends Plugin
{
    protected string $name = 'My Awesome Plugin';
    protected string $version = '1.0.0';
    protected array $dependencies = ['CorePlugin'];

    public function install(): void
    {
        // Run installation logic
        // Create tables, default settings, etc.
    }

    public function uninstall(): void
    {
        // Cleanup
    }

    public function boot(): void
    {
        // Register hooks
        $this->addHook('user.created', function($user) {
            // Do something when user created
        });

        // Register routes, services, etc.
    }
}
```

**Plugin Manager:**
```php
$pluginManager = new PluginManager($container);

// Discover plugins
$plugins = $pluginManager->discover();

// Install plugin
$pluginManager->install('My Awesome Plugin');

// Activate plugin
$pluginManager->activate('My Awesome Plugin');

// Boot all active plugins
$pluginManager->bootPlugins();
```

---

## 4. 🎣 Hook System

ระบบ Hook แบบ WordPress action/filter:

**Actions:**
```php
use NeoPhp\Plugin\HookManager;

// Register action hook
HookManager::addAction('user.created', function($user) {
    logger()->info("User {$user['name']} created");
}, 10, 1);

// Execute action
HookManager::doAction('user.created', $user);
```

**Filters:**
```php
// Register filter hook
HookManager::addFilter('user.name', function($name) {
    return strtoupper($name);
}, 10, 1);

// Apply filter
$userName = HookManager::applyFilters('user.name', $user->name);
```

**Plugin Hooks:**
```php
class MyPlugin extends Plugin
{
    public function boot(): void
    {
        // Action: Do something when event happens
        HookManager::addAction('app.booted', [$this, 'onAppBooted']);
        
        // Filter: Modify data
        HookManager::addFilter('user.data', [$this, 'filterUserData']);
    }

    public function onAppBooted(): void
    {
        // Plugin logic
    }

    public function filterUserData(array $data): array
    {
        $data['plugin_field'] = 'value';
        return $data;
    }
}
```

---

## 5. 📊 Metadata-Driven Architecture

ระบบ Metadata แบบ Neonex Core - **นี่คือหัวใจสำคัญ!**

**Define Model with Metadata:**
```php
use NeoPhp\Metadata\{Table, Field, HasMany, BelongsTo};

#[Table('products')]
class Product
{
    #[Field('id', type: 'integer', primary: true, autoIncrement: true)]
    public int $id;

    #[Field('name', 
        type: 'string', 
        length: 255,
        nullable: false,
        validation: ['required', 'max:255'],
        label: 'Product Name',
        searchable: true,
        sortable: true
    )]
    public string $name;

    #[Field('price',
        type: 'decimal',
        precision: 10,
        scale: 2,
        min: 0,
        validation: ['required', 'numeric', 'min:0'],
        label: 'Price (THB)'
    )]
    public float $price;

    #[Field('description',
        type: 'text',
        nullable: true,
        inputType: 'textarea',
        placeholder: 'Product description...'
    )]
    public ?string $description;

    #[Field('category_id',
        type: 'integer',
        foreignTable: 'categories',
        foreignKey: 'id',
        onDelete: 'CASCADE'
    )]
    public int $category_id;

    #[Field('image',
        type: 'file',
        mimes: ['jpg', 'png', 'webp'],
        maxFileSize: 2048,
        validation: ['file', 'mimes:jpg,png,webp', 'max:2048']
    )]
    public ?string $image;

    #[Field('status',
        type: 'enum',
        enum: ['active' => 'Active', 'inactive' => 'Inactive'],
        default: 'active',
        inputType: 'select'
    )]
    public string $status;

    #[BelongsTo(model: Category::class, foreignKey: 'category_id')]
    public function category() {}

    #[HasMany(model: Review::class, foreignKey: 'product_id')]
    public function reviews() {}
}
```

**Metadata Repository:**
```php
$metadataRepo = new MetadataRepository($cache);

// Get model metadata
$metadata = $metadataRepo->getModelMetadata(Product::class);

/*
Returns:
[
    'table' => 'products',
    'primaryKey' => 'id',
    'fields' => [
        'name' => [
            'type' => 'string',
            'validation' => ['required', 'max:255'],
            'label' => 'Product Name',
            'searchable' => true,
            ...
        ],
        ...
    ],
    'relationships' => [
        'category' => ['type' => 'BelongsTo', 'model' => Category::class],
        'reviews' => ['type' => 'HasMany', 'model' => Review::class]
    ]
]
*/

// Get validation rules automatically
$rules = $metadataRepo->getValidationRules(Product::class);
/*
Returns:
[
    'name' => 'required|max:255',
    'price' => 'required|numeric|min:0',
    'image' => 'file|mimes:jpg,png,webp|max:2048',
    ...
]
*/
```

---

## 6. 📝 Dynamic Form Generator

สร้าง Forms อัตโนมัติจาก Metadata:

```php
$formBuilder = new FormBuilder($metadataRepo);

// Generate form HTML from model
$formHtml = $formBuilder->make(Product::class, [
    'action' => '/products',
    'method' => 'POST',
    'values' => $product->toArray(), // For edit form
    'errors' => $validator->errors() // Show validation errors
]);

echo $formHtml;
/*
Generates:
<form method="POST" action="/products" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Product Name<span class="required">*</span></label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="price">Price (THB)<span class="required">*</span></label>
        <input type="number" name="price" id="price" class="form-control" min="0" required>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" class="form-control" rows="4"></textarea>
    </div>
    
    <div class="form-group">
        <label for="image">Image</label>
        <input type="file" name="image" id="image" accept=".jpg,.png,.webp">
    </div>
    
    <div class="form-group">
        <label for="status">Status<span class="required">*</span></label>
        <select name="status" id="status" class="form-control">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
*/
```

**ใน Blade:**
```php
@formFor('Product', ['action' => '/products', 'method' => 'POST'])
// Auto-generated form
@endformFor
```

---

## 7. ✅ Metadata-Driven Validation

Validation อัตโนมัติจาก Metadata:

```php
// Extract rules from metadata
$metadata = $metadataRepo->getModelMetadata(Product::class);
$rules = $metadataRepo->getValidationRules(Product::class);

// Validate automatically
$validator = validator($_POST, $rules);

if ($validator->fails()) {
    return back()->withErrors($validator->errors());
}

// Or use in controller
public function store()
{
    $rules = metadata(Product::class)->getValidationRules();
    $validator = validator(request()->all(), $rules);
    
    if ($validator->passes()) {
        Product::create($validator->validated());
    }
}
```

---

## 🎯 เปรียบเทียบ: ก่อนและหลัง

### ❌ ก่อน (Hard-coded):

```php
// ต้องเขียนเอง
class ProductController
{
    public function create()
    {
        return view('products.create');
    }

    public function store()
    {
        // Hard-coded validation
        $validator = validator($_POST, [
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'file|mimes:jpg,png|max:2048',
            ...
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        Product::create($validator->validated());
    }
}

// ต้องเขียน form เอง
<!-- products/create.blade.php -->
<form method="POST" action="/products">
    <input type="text" name="name" required>
    <input type="number" name="price" min="0" required>
    <input type="file" name="image" accept=".jpg,.png">
    ...
    <button type="submit">Submit</button>
</form>
```

### ✅ หลัง (Metadata-driven):

```php
// Auto-everything!
class ProductController
{
    public function create()
    {
        $form = form()->make(Product::class, [
            'action' => '/products',
            'method' => 'POST'
        ]);
        
        return view('products.create', compact('form'));
    }

    public function store()
    {
        // Auto validation from metadata
        $rules = metadata(Product::class)->getValidationRules();
        $validator = validator(request()->all(), $rules);

        if ($validator->passes()) {
            Product::create($validator->validated());
            return redirect('/products');
        }

        // Auto form with errors
        $form = form()->make(Product::class, [
            'action' => '/products',
            'method' => 'POST',
            'values' => request()->all(),
            'errors' => $validator->errors()
        ]);

        return view('products.create', compact('form'));
    }
}

// Auto-generated form
<!-- products/create.blade.php -->
{!! $form !!}
```

---

## 🚀 การใช้งานแบบ Neonex Core

### 1. Bootstrap Application

```php
// bootstrap/app.php
$container = new Container();

// Register providers
$providerManager = new ProviderManager($container);
$providerManager->registerProviders([
    DatabaseServiceProvider::class,
    CacheServiceProvider::class,
    QueueServiceProvider::class,
    // ... other providers
]);

// Boot providers
$providerManager->bootProviders();

// Initialize plugin system
$pluginManager = new PluginManager($container);
$plugins = $pluginManager->discover();

// Boot active plugins
$pluginManager->bootPlugins();

// Initialize metadata repository
$metadataRepo = new MetadataRepository($container->make('cache'));
$container->instance(MetadataRepository::class, $metadataRepo);

return $container;
```

### 2. สร้าง Plugin

```php
// plugins/BlogPlugin/Plugin.php
namespace Plugins\BlogPlugin;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Plugin\HookManager;

class BlogPlugin extends Plugin
{
    protected string $name = 'Blog Plugin';
    protected string $version = '1.0.0';

    public function install(): void
    {
        // Create blog tables
        $db = app('db');
        $db->execute("
            CREATE TABLE blog_posts (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function boot(): void
    {
        // Register routes
        Route::get('/blog', [BlogController::class, 'index']);
        Route::get('/blog/{id}', [BlogController::class, 'show']);

        // Register hooks
        HookManager::addAction('post.created', function($post) {
            logger()->info("Blog post created: {$post['title']}");
        });

        // Register service provider
        app()->make(ProviderManager::class)->register(BlogServiceProvider::class);
    }
}
```

### 3. Metadata-Driven CRUD

```php
// ตัวอย่างการสร้าง CRUD อัตโนมัติ
class GenericCRUDController
{
    protected MetadataRepository $metadata;
    protected FormBuilder $formBuilder;

    public function __construct()
    {
        $this->metadata = app(MetadataRepository::class);
        $this->formBuilder = app(FormBuilder::class);
    }

    public function index(string $model)
    {
        $items = $model::paginate(25);
        $meta = $this->metadata->getModelMetadata($model);
        
        return view('admin.generic.index', [
            'items' => $items,
            'metadata' => $meta
        ]);
    }

    public function create(string $model)
    {
        $form = $this->formBuilder->make($model, [
            'action' => "/admin/{$model}",
            'method' => 'POST'
        ]);
        
        return view('admin.generic.create', compact('form'));
    }

    public function store(string $model)
    {
        $rules = $this->metadata->getValidationRules($model);
        $validator = validator(request()->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $model::create($validator->validated());
        
        return redirect("/admin/{$model}")->with('success', 'Created successfully');
    }
}
```

---

## 📚 สรุป: NeoPhp ตอนนี้

### ✅ เหมือน Neonex Core:
- ✅ Pure Contract/Interface layer
- ✅ Service Provider system
- ✅ Plugin architecture  
- ✅ Hook system (action/filter)
- ✅ Metadata-driven architecture
- ✅ Deferred loading
- ✅ Dependency injection
- ✅ Auto-discovery

### 🎯 Features พิเศษ:
- ✅ Metadata attributes (#[Table], #[Field])
- ✅ Dynamic form generation
- ✅ Auto validation from metadata
- ✅ Plugin install/uninstall system
- ✅ Hook manager
- ✅ Provider manager with dependencies

### 🚀 Next Steps:

เมื่อต้องการสร้าง **Admin Panel Generator** ต่อ:

1. **CLI Framework** - สำหรับ `php artisan make:*` commands
2. **Code Generator** - Generate controllers, models, views
3. **CRUD Generator** - Auto-generate CRUD from metadata
4. **Migration System** - Database versioning
5. **Seeder System** - Fake data generation

---

**ตอนนี้ NeoPhp พร้อมเป็น Foundation Framework แบบ Neonex Core แล้วครับ!** 🎉

แค่ยังขาด CLI tools สำหรับ code generation ซึ่งเป็น phase ถัดไปครับ
