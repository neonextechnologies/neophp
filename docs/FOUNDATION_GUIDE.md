# Foundation Framework Guide

This guide covers the core architecture patterns in NeoPhp.

## Contracts

NeoPhp uses interfaces to define core functionality. This lets you swap implementations without changing your code.

### Available Contracts

```php
src/Contracts/
├── DatabaseInterface.php
├── CacheInterface.php
├── QueueInterface.php
├── LoggerInterface.php
├── StorageInterface.php
├── MailerInterface.php
├── ValidatorInterface.php
├── ServiceProviderInterface.php
├── PluginInterface.php
└── MetadataInterface.php
```

### Example Usage

```php
use NeoPhp\Contracts\DatabaseInterface;
use NeoPhp\Contracts\CacheInterface;

class UserService
{
    public function __construct(
        private DatabaseInterface $db,
        private CacheInterface $cache
    ) {}
    
    public function find(int $id): ?array {
        return $this->cache->remember("user.$id", function() use ($id) {
            return $this->db->query('SELECT * FROM users WHERE id = ?', [$id]);
        });
    }
}
```

## Service Providers

Service providers are where you register and bootstrap your services.

### Creating a Provider

```php
use NeoPhp\Foundation\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    protected array $provides = ['payment'];
    protected bool $defer = true;
    protected array $dependencies = [DatabaseServiceProvider::class];

    public function register(): void
    {
        $this->singleton('payment', function($app) {
            return new StripePayment($app->make('db'));
        });
    }

    public function boot(): void
    {
        // Bootstrap after all providers are registered
    }
}
```

### Provider Manager

```php
$providerManager = new ProviderManager($container);

// Register providers manually
$providerManager->registerProviders([
    DatabaseServiceProvider::class,
    CacheServiceProvider::class,
    PaymentServiceProvider::class
]);

// Or auto-discover from directory
$providers = $providerManager->discover('app/Providers');

// Boot all registered providers
$providerManager->bootProviders();
```

### Provider Features

- **Deferred Loading** - Set `$defer = true` to load only when needed
- **Dependencies** - Declare provider dependencies in `$dependencies`
- **Service Binding** - Use `singleton()`, `bind()`, or `instance()` methods

## Plugins

Plugins let you extend functionality without modifying core code.

### Creating a Plugin

```php
namespace Plugins\Blog;

use NeoPhp\Plugin\Plugin;

class BlogPlugin extends Plugin
{
    protected string $name = 'Blog';
    protected string $version = '1.0.0';
    protected array $dependencies = [];

    public function install(): void
    {
        Schema::create('posts', function($table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function uninstall(): void
    {
        Schema::dropIfExists('posts');
    }

    public function boot(): void
    {
        // Register hooks
        HookManager::addAction('app.boot', [$this, 'registerRoutes']);
        
        // Register services
        $this->app->singleton('blog', fn() => new BlogService());
    }
    
    public function registerRoutes(): void
    {
        Route::get('/blog', [BlogController::class, 'index']);
    }
}
```

### Plugin Manager

```php
$pluginManager = new PluginManager($container);

// Discover available plugins
$plugins = $pluginManager->discover();

// Install and activate
$pluginManager->install('Blog');
$pluginManager->activate('Blog');

// Boot all active plugins
$pluginManager->bootPlugins();
```

## Hooks

The hook system lets plugins interact with your application.

### Actions
```php
use NeoPhp\Plugin\HookManager;

// Register an action
HookManager::addAction('user.created', function($user) {
    logger()->info("User {$user['name']} created");
}, 10, 1);

// Trigger the action
HookManager::doAction('user.created', $user);
```

### Filters

```php
// Register a filter
HookManager::addFilter('user.name', function($name) {
    return strtoupper($name);
}, 10, 1);

// Apply the filter
$userName = HookManager::applyFilters('user.name', $user->name);
```

### Using Hooks in Plugins

```php
class BlogPlugin extends Plugin
{
    public function boot(): void
    {
        HookManager::addAction('app.booted', [$this, 'onAppBooted']);
        HookManager::addFilter('user.data', [$this, 'filterUserData']);
    }

    public function onAppBooted(): void
    {
        // Do something when app is booted
    }

    public function filterUserData(array $data): array
    {
        $data['from_blog_plugin'] = true;
        return $data;
    }
}
```

## Metadata System

Define your models using PHP 8 attributes. This metadata can be used to generate forms, validation rules, and database schemas.

### Basic Example

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
        label: 'Product Name'
    )]
    public string $name;

    #[Field('price',
        type: 'decimal',
        precision: 10,
        scale: 2,
        validation: ['required', 'numeric', 'min:0']
    )]
    public float $price;

    #[BelongsTo(model: Category::class, foreignKey: 'category_id')]
    public function category() {}

    #[HasMany(model: Review::class, foreignKey: 'product_id')]
    public function reviews() {}
}
```

### Reading Metadata
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

```php
$metadataRepo = new MetadataRepository($cache);

// Get all metadata for a model
$metadata = $metadataRepo->getModelMetadata(Product::class);

// Returns structure like:
// [
//     'table' => 'products',
//     'primaryKey' => 'id',
//     'fields' => [...],
//     'relationships' => [...]
// ]

// Extract validation rules automatically
$rules = $metadataRepo->getValidationRules(Product::class);

// Returns:
// [
//     'name' => 'required|max:255',
//     'price' => 'required|numeric|min:0',
//     ...
// ]
```

### Available Attributes

- `#[Table('table_name')]` - Define table name
- `#[Field(...)]` - Define field properties
- `#[Validation(['rule1', 'rule2'])]` - Validation rules
- `#[HasMany(...)]` - One-to-many relationship
- `#[BelongsTo(...)]` - Many-to-one relationship
- `#[BelongsToMany(...)]` - Many-to-many relationship
- `#[MorphTo(...)]` - Polymorphic relationship

## Form Builder

Generate HTML forms from metadata automatically.

```php
$formBuilder = new FormBuilder($metadataRepo);

// Generate form for creating/editing
$formHtml = $formBuilder->make(Product::class, [
    'action' => '/products',
    'method' => 'POST',
    'values' => $product ?? [],
    'errors' => $errors ?? []
]);

echo $formHtml;
```

The form builder:
- Generates appropriate input types based on field metadata
- Adds validation attributes (required, min, max, etc.)
- Pre-fills values for edit forms
- Displays validation errors
- Handles file uploads

### Custom Form Fields

```php
#[Field('status',
    type: 'enum',
    enum: ['draft' => 'Draft', 'published' => 'Published'],
    inputType: 'radio'
)]
public string $status;
```

This generates radio buttons instead of a select dropdown.

## Validation

Extract validation rules from metadata:

```php
$rules = $metadataRepo->getValidationRules(Product::class);

$validator = validator($_POST, $rules);

if ($validator->fails()) {
    return back()->withErrors($validator->errors());
}

$validated = $validator->validated();
```

## Complete Example

Here's how everything works together:// Or use in controller
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

## 🎯 Comparison: Before and After

### ❌ Before (Hard-coded):

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
```php
// Define your model with metadata
#[Table('products')]
class Product
{
    #[Field('name', type: 'string', validation: ['required', 'max:255'])]
    public string $name;

    #[Field('price', type: 'decimal', validation: ['required', 'numeric', 'min:0'])]
    public float $price;

    #[BelongsTo(model: Category::class, foreignKey: 'category_id')]
    public function category() {}
}

// Create a service provider
class ProductServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->singleton('products', fn() => new ProductRepository(
            $this->app->make('db')
        ));
    }
}

// Create a plugin
class ShopPlugin extends Plugin
{
    protected string $name = 'Shop';
    
    public function install(): void {
        Schema::create('products', function($table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }
    
    public function boot(): void {
        Route::get('/shop', [ShopController::class, 'index']);
        
        HookManager::addAction('product.created', function($product) {
            logger()->info("Product created: {$product['name']}");
        });
    }
}

// Controller with auto-validation
class ProductController
{
    public function store()
    {
        $rules = metadata(Product::class)->getValidationRules();
        $validator = validator(request()->all(), $rules);

        if ($validator->passes()) {
            Product::create($validator->validated());
            return redirect('/products');
        }

        return back()->withErrors($validator->errors());
    }
}

// Generate form from metadata
$form = form()->make(Product::class, [
    'action' => '/products',
    'method' => 'POST'
]);
```

## Best Practices

### Contract Usage

Always type-hint interfaces, not concrete classes:

```php
// Good
public function __construct(private DatabaseInterface $db) {}

// Bad
public function __construct(private PDODatabase $db) {}
```

### Service Provider Organization

Group related services in one provider:

```php
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton('stripe', fn() => new StripePayment());
        $this->app->singleton('paypal', fn() => new PayPalPayment());
        $this->app->singleton('payment', fn() => new PaymentGateway(
            $this->app->make('stripe'),
            $this->app->make('paypal')
        ));
    }
}
```

### Plugin Dependencies

Declare dependencies explicitly:

```php
class AdvancedBlogPlugin extends Plugin
{
    protected string $name = 'Advanced Blog';
    protected array $dependencies = ['Blog', 'Media'];
    
    // Plugin will only load after Blog and Media plugins
}
```

### Metadata Best Practices

Keep validation rules in metadata for reusability:

```php
// Example: Auto-generate CRUD
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
```php
#[Field('name', 
    type: 'string',
    validation: ['required', 'max:255'], // Rules here
    label: 'Product Name'
)]
public string $name;
```

This keeps your validation logic with your model definition, making it easier to maintain.

## Summary

NeoPhp provides:

- **Contracts** - Interface-based architecture
- **Service Providers** - Modular service registration
- **Plugins** - Extensible architecture with hooks
- **Metadata** - PHP 8 attributes for declarative models
- **Form Builder** - Auto-generate forms from metadata
- **CLI Tools** - Code generation and migrations

This foundation lets you build applications without being locked into specific implementations. You can swap any component by implementing the appropriate contract.

For CLI tools and code generation, see the [CLI Guide](CLI_GUIDE.md).
