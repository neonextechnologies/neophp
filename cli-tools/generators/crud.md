# CRUD Generator

The CRUD generator creates a complete Create, Read, Update, Delete system in seconds - including model, controller, views, and routes.

## Basic Usage

```bash
# Generate complete CRUD
php neo make:crud Product

# Generate with all options
php neo make:crud Product --api --auth --search
```

## Command Syntax

```bash
php neo make:crud [name] [options]
```

### Arguments

- `name` - Model name (e.g., Product, BlogPost, User)

### Options

- `-a, --api` - Generate API endpoints
- `--auth` - Add authentication/authorization
- `--search` - Include search functionality
- `--export` - Add export features (CSV, PDF)
- `--soft-deletes` - Use soft deletes
- `--force` - Overwrite existing files

## What Gets Generated

Running `php neo make:crud Product` creates:

```
app/
├── Models/
│   └── Product.php                    # Model with metadata
├── Controllers/
│   └── ProductController.php          # Full CRUD controller
database/
├── migrations/
│   └── xxx_create_products_table.php  # Migration
views/
├── products/
│   ├── index.php                      # List view
│   ├── create.php                     # Create form
│   ├── edit.php                       # Edit form
│   └── show.php                       # Detail view
routes/
└── web.php                            # Routes added
```

## Generated Files

### 1. Model (app/Models/Product.php)

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('products')]
#[Timestamps]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Validation('required|max:255')]
    #[FormField(type: 'text', label: 'Product Name')]
    public string $name;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validation('required|numeric|min:0')]
    #[FormField(type: 'number', label: 'Price', step: '0.01')]
    public float $price;
    
    #[Field(type: 'text', nullable: true)]
    #[FormField(type: 'textarea', label: 'Description', rows: 5)]
    public ?string $description;
}
```

### 2. Controller (app/Controllers/ProductController.php)

```php
<?php

namespace App\Controllers;

use NeoPhp\Foundation\Controller;
use NeoPhp\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(15);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable',
        ]);

        $product = Product::create($validated);

        return redirect("/products/{$product->id}")
            ->with('success', 'Product created successfully');
    }

    public function show(int $id)
    {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit(int $id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable',
        ]);

        $product->update($validated);

        return redirect("/products/{$product->id}")
            ->with('success', 'Product updated successfully');
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect('/products')
            ->with('success', 'Product deleted successfully');
    }
}
```

### 3. Views

**index.php** - List all products:

```php
<h1>Products</h1>

<a href="/products/create" class="btn btn-primary">Create New Product</a>

<?php if (session('success')): ?>
    <div class="alert alert-success"><?= session('success') ?></div>
<?php endif; ?>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product->id ?></td>
                <td><?= $product->name ?></td>
                <td>$<?= number_format($product->price, 2) ?></td>
                <td>
                    <a href="/products/<?= $product->id ?>" class="btn btn-sm btn-info">View</a>
                    <a href="/products/<?= $product->id ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                    <form action="/products/<?= $product->id ?>" method="POST" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $products->links() ?>
```

**create.php** - Create form:

```php
<h1>Create Product</h1>

<form method="POST" action="/products">
    <?= csrf_field() ?>
    
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
        <?php if ($errors->has('name')): ?>
            <div class="text-danger"><?= $errors->first('name') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="price">Price *</label>
        <input type="number" name="price" class="form-control" value="<?= old('price') ?>" step="0.01" required>
        <?php if ($errors->has('price')): ?>
            <div class="text-danger"><?= $errors->first('price') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" class="form-control" rows="5"><?= old('description') ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Create</button>
    <a href="/products" class="btn btn-secondary">Cancel</a>
</form>
```

### 4. Routes (routes/web.php)

```php
// Product CRUD routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/create', [ProductController::class, 'create']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/{id}/edit', [ProductController::class, 'edit']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// Or use resource route
Route::resource('products', ProductController::class);
```

## Advanced Features

### With API Endpoints

```bash
php neo make:crud Product --api
```

Generates additional API controller:

```php
<?php

namespace App\Controllers\Api;

use NeoPhp\Foundation\Controller;
use NeoPhp\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json(['data' => $products]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product = Product::create($validated);
        return response()->json(['data' => $product], 201);
    }

    public function show(int $id)
    {
        $product = Product::findOrFail($id);
        return response()->json(['data' => $product]);
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);
        return response()->json(['data' => $product]);
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
}
```

### With Authentication

```bash
php neo make:crud Product --auth
```

Adds authentication middleware:

```php
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:create,Product')->only(['create', 'store']);
        $this->middleware('can:update,product')->only(['edit', 'update']);
        $this->middleware('can:delete,product')->only(['destroy']);
    }
    
    // CRUD methods...
}
```

### With Search

```bash
php neo make:crud Product --search
```

Adds search functionality:

```php
public function index(Request $request)
{
    $query = Product::query();
    
    if ($search = $request->get('search')) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
    }
    
    $products = $query->orderBy('created_at', 'desc')->paginate(15);
    
    return view('products.index', compact('products'));
}
```

Search form in view:

```php
<form method="GET" action="/products" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= request('search') ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
</form>
```

### With Export

```bash
php neo make:crud Product --export
```

Adds export functionality:

```php
public function export(Request $request)
{
    $format = $request->get('format', 'csv');
    $products = Product::all();
    
    if ($format === 'csv') {
        return $this->exportCsv($products);
    } elseif ($format === 'pdf') {
        return $this->exportPdf($products);
    }
}

private function exportCsv($products)
{
    $filename = "products_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename={$filename}");
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Price', 'Description']);
    
    foreach ($products as $product) {
        fputcsv($output, [
            $product->id,
            $product->name,
            $product->price,
            $product->description
        ]);
    }
    
    fclose($output);
    exit;
}
```

## Complete Example: Blog System

```bash
php neo make:crud Post --api --auth --search --soft-deletes
```

Generates complete blog post CRUD with:
- Model with metadata (title, slug, content, status, etc.)
- Web controller with full CRUD
- API controller with JSON responses
- Authentication and authorization
- Search by title and content
- Soft deletes (deleted_at column)
- All necessary views
- Routes for web and API

## Customization

### Define Fields Inline

```bash
php neo make:crud Product --fields="name:string,sku:string:unique,price:decimal,stock:integer"
```

### Specify Relationships

```bash
php neo make:crud Post --relationships="author:belongsTo:User,comments:hasMany:Comment,tags:belongsToMany:Tag"
```

### Custom Template

```bash
php neo make:crud Product --template=custom-crud
```

## Interactive Mode

```bash
php neo make:crud

# Prompts:
# Model name: Product
# Generate API controller? (yes/no): yes
# Add authentication? (yes/no): yes
# Include search? (yes/no): yes
# Add export features? (yes/no): no
# Use soft deletes? (yes/no): no
# Generating CRUD...
```

## Batch CRUD Generation

Generate multiple CRUD systems:

```bash
# E-commerce
php neo make:crud Product,Category,Brand,Order --api

# Blog
php neo make:crud Post,Category,Tag,Comment --auth --search

# CMS
php neo make:crud Page,Menu,Widget --soft-deletes
```

## Best Practices

### 1. Use CRUD for Simple Resources

```bash
# Good ✅ - Simple CRUD resources
php neo make:crud Product
php neo make:crud Category
php neo make:crud Tag

# For complex logic, customize after generation
```

### 2. Add Authentication for Protected Resources

```bash
# Good ✅
php neo make:crud Product --auth

# Protects create, edit, delete operations
```

### 3. Include Search for Large Datasets

```bash
# Good ✅
php neo make:crud Product --search

# Makes finding items easier
```

### 4. Use API for Mobile/SPA

```bash
# Good ✅
php neo make:crud Product --api

# Provides REST API alongside web interface
```

### 5. Customize After Generation

CRUD generators create boilerplate. Customize for your needs:

```php
// Add custom business logic
public function store(Request $request)
{
    $validated = $request->validate([...]);
    
    // Custom logic
    $validated['slug'] = Str::slug($validated['name']);
    $validated['user_id'] = auth()->id();
    
    $product = Product::create($validated);
    
    // Send notifications, clear cache, etc.
    event(new ProductCreated($product));
    
    return redirect("/products/{$product->id}");
}
```

## Next Steps

- [Model Generator](model.md)
- [Controller Generator](controller.md)
- [Form Generator](form.md)
- [View Generator](view.md)
- [Routes Configuration](../routing.md)
