# Quick Start

This guide will help you create your first NeoPhp application in minutes.

## Create Your First Model

Generate a model with migration:

```bash
php neo make:model Product -m
```

This creates:
* `app/Models/Product.php`
* `database/migrations/YYYY_MM_DD_HHMMSS_create_products_table.php`

## Define Your Model

Edit `app/Models/Product.php`:

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Metadata\{Table, Field, Validation};

#[Table('products')]
class Product extends Model
{
    #[Field(type: 'integer', primary: true, autoIncrement: true)]
    public int $id;

    #[Field(type: 'string', length: 255)]
    #[Validation(['required', 'min:3', 'max:255'])]
    public string $name;

    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validation(['required', 'numeric', 'min:0'])]
    public float $price;

    #[Field(type: 'text', nullable: true)]
    public ?string $description;

    #[Field(type: 'timestamp')]
    public string $created_at;

    #[Field(type: 'timestamp')]
    public string $updated_at;
}
```

## Create Migration

Edit the generated migration file:

```php
<?php

use NeoPhp\Database\Migrations\Migration;
use NeoPhp\Database\Schema\Schema;
use NeoPhp\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

## Run Migration

```bash
php neo migrate
```

## Create Controller

```bash
php neo make:controller ProductController
```

Edit `app/Controllers/ProductController.php`:

```php
<?php

namespace App\Controllers;

use NeoPhp\Http\Controller;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function store(Request $request): Response
    {
        // Auto-validation from metadata
        $rules = metadata(Product::class)->getValidationRules();
        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($validator->validated());
        return response()->json($product, 201);
    }

    public function show(Request $request, int $id): Response
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, int $id): Response
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $rules = metadata(Product::class)->getValidationRules();
        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($validator->validated());
        return response()->json($product);
    }

    public function destroy(Request $request, int $id): Response
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
```

## Generate Form

Create a form automatically from your model:

```php
use NeoPhp\Forms\FormBuilder;

$formBuilder = new FormBuilder();
$formHtml = $formBuilder->make(Product::class, [
    'action' => '/products',
    'method' => 'POST'
]);

echo $formHtml;
```

This generates a complete HTML form with:
* Proper input types based on metadata
* Validation attributes
* Labels and placeholders
* Error handling

## Create a Plugin

```bash
php neo make:plugin Shop
```

Edit `plugins/shop/ShopPlugin.php`:

```php
<?php

namespace Plugins\Shop;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Plugin\HookManager;

class ShopPlugin extends Plugin
{
    protected string $name = 'shop';
    protected string $version = '1.0.0';

    public function boot(): void
    {
        // Add custom functionality
        HookManager::addAction('product.created', function($product) {
            logger()->info("New product: {$product->name}");
        });

        // Register routes
        Route::get('/shop', [ShopController::class, 'index']);
    }
}
```

## Next Steps

* Learn about [Service Providers](../core-concepts/service-providers.md)
* Explore [Metadata System](../core-concepts/metadata.md)
* Build with [Plugins](../core-concepts/plugins.md)
* Use [CLI Tools](../cli/introduction.md)

## Full Example

Here's a complete example combining everything:

```php
// 1. Define Model with Metadata
#[Table('products')]
class Product extends Model
{
    #[Field(type: 'string')]
    #[Validation(['required'])]
    public string $name;
}

// 2. Create Migration
php neo make:model Product -m
php neo migrate

// 3. Generate Form
$form = form()->make(Product::class);

// 4. Auto Validate
$rules = metadata(Product::class)->getValidationRules();
$validator = validator($_POST, $rules);

// 5. Save
if ($validator->passes()) {
    Product::create($validator->validated());
}
```

That's it! You've created a complete CRUD system with just a few lines of code.
