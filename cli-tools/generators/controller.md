# Controller Generator

The controller generator creates controller classes with pre-built CRUD methods, making it easy to handle HTTP requests and business logic.

## Basic Usage

```bash
# Generate a simple controller
php neo make:controller ProductController

# Generate with resource methods
php neo make:controller ProductController --resource

# Generate for a model
php neo make:controller ProductController --model=Product
```

## Command Syntax

```bash
php neo make:controller [name] [options]
```

### Arguments

- `name` - The name of the controller (e.g., ProductController, UserController)

### Options

- `-r, --resource` - Generate resource controller with CRUD methods
- `-m, --model=` - Associate with a model
- `-a, --api` - Generate API controller (JSON responses)
- `--force` - Overwrite existing file
- `--invokable` - Generate single-action controller

## Examples

### Simple Controller

```bash
php neo make:controller HomeController
```

Creates `app/Controllers/HomeController.php`:

```php
<?php

namespace App\Controllers;

use NeoPhp\Foundation\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('home.index');
    }
}
```

### Resource Controller

```bash
php neo make:controller ProductController --resource
```

Creates controller with all CRUD methods:

```php
<?php

namespace App\Controllers;

use NeoPhp\Foundation\Controller;
use NeoPhp\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation and storage logic
        return redirect('/products');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return view('products.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        return view('products.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        // Validation and update logic
        return redirect('/products');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        // Delete logic
        return redirect('/products');
    }
}
```

### Controller with Model

```bash
php neo make:controller ProductController --model=Product --resource
```

Creates controller with model integration:

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
        $products = Product::all();
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
        $product = Product::find($id);
        
        if (!$product) {
            return response()->notFound();
        }

        return view('products.show', compact('product'));
    }

    public function edit(int $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->notFound();
        }

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->notFound();
        }

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
        $product = Product::find($id);
        
        if (!$product) {
            return response()->notFound();
        }

        $product->delete();

        return redirect('/products')
            ->with('success', 'Product deleted successfully');
    }
}
```

### API Controller

```bash
php neo make:controller ProductController --api --model=Product
```

Creates API controller with JSON responses:

```php
<?php

namespace App\Controllers;

use NeoPhp\Foundation\Controller;
use NeoPhp\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
```

### Invokable Controller

Single-action controller:

```bash
php neo make:controller SendEmailController --invokable
```

```php
<?php

namespace App\Controllers;

use NeoPhp\Foundation\Controller;
use NeoPhp\Http\Request;

class SendEmailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Single action logic
        return response()->json(['message' => 'Email sent']);
    }
}
```

## Controller with Dependency Injection

```bash
php neo make:controller ProductController --model=Product --resource --inject
```

```php
<?php

namespace App\Controllers;

use NeoPhp\Foundation\Controller;
use NeoPhp\Http\Request;
use App\Models\Product;
use NeoPhp\Contracts\Database;
use NeoPhp\Contracts\Cache;
use NeoPhp\Contracts\Logger;

class ProductController extends Controller
{
    public function __construct(
        private Database $db,
        private Cache $cache,
        private Logger $logger
    ) {}

    public function index()
    {
        // Try cache first
        $products = $this->cache->remember('products.all', 3600, function() {
            return Product::all();
        });

        $this->logger->info('Products list viewed');

        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $this->db->beginTransaction();

        try {
            $product = Product::create($validated);
            
            // Clear cache
            $this->cache->forget('products.all');
            
            $this->db->commit();
            
            $this->logger->info("Product created: {$product->id}");

            return redirect("/products/{$product->id}")
                ->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            $this->db->rollback();
            
            $this->logger->error("Product creation failed: {$e->getMessage()}");
            
            return back()
                ->with('error', 'Failed to create product')
                ->withInput();
        }
    }
}
```

## Controller Namespaces

Generate controllers in subdirectories:

```bash
# Creates app/Controllers/Admin/ProductController.php
php neo make:controller Admin/ProductController --resource

# Creates app/Controllers/Api/V1/ProductController.php
php neo make:controller Api/V1/ProductController --api

# Creates app/Controllers/Shop/CartController.php
php neo make:controller Shop/CartController
```

## Advanced Examples

### With Form Generation

```bash
php neo make:controller ProductController --model=Product --resource --form
```

Includes form handling:

```php
public function create()
{
    $form = FormGenerator::fromModel(Product::class);
    return view('products.create', compact('form'));
}

public function edit(int $id)
{
    $product = Product::find($id);
    $form = FormGenerator::fromModel(Product::class, $product);
    return view('products.edit', compact('product', 'form'));
}
```

### With Authorization

```bash
php neo make:controller ProductController --model=Product --resource --auth
```

```php
public function edit(int $id)
{
    $product = Product::find($id);
    
    if (!$product) {
        return response()->notFound();
    }
    
    // Check authorization
    if (!auth()->user()->can('update', $product)) {
        return response()->forbidden();
    }
    
    return view('products.edit', compact('product'));
}
```

### With Pagination

```bash
php neo make:controller ProductController --model=Product --resource --paginate
```

```php
public function index(Request $request)
{
    $perPage = $request->get('per_page', 15);
    $products = Product::paginate($perPage);
    
    return view('products.index', compact('products'));
}
```

### With Search and Filter

```bash
php neo make:controller ProductController --model=Product --resource --search
```

```php
public function index(Request $request)
{
    $query = Product::query();
    
    // Search
    if ($search = $request->get('search')) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
    }
    
    // Filter by category
    if ($category = $request->get('category')) {
        $query->where('category_id', $category);
    }
    
    // Filter by price range
    if ($minPrice = $request->get('min_price')) {
        $query->where('price', '>=', $minPrice);
    }
    
    if ($maxPrice = $request->get('max_price')) {
        $query->where('price', '<=', $maxPrice);
    }
    
    // Sort
    $sortBy = $request->get('sort', 'created_at');
    $sortDir = $request->get('dir', 'desc');
    $query->orderBy($sortBy, $sortDir);
    
    $products = $query->paginate(15);
    
    return view('products.index', compact('products'));
}
```

## Interactive Mode

```bash
php neo make:controller

# Prompts:
# Controller name: ProductController
# Resource controller? (yes/no): yes
# Model name (optional): Product
# API controller? (yes/no): no
# Generate with authorization? (yes/no): no
# Creating controller...
```

## Controller Traits

Generate with common traits:

```bash
php neo make:controller ProductController --traits=Authorizable,Cacheable
```

```php
<?php

namespace App\Controllers;

use NeoPhp\Foundation\Controller;
use App\Traits\Authorizable;
use App\Traits\Cacheable;

class ProductController extends Controller
{
    use Authorizable, Cacheable;
    
    // Controller methods
}
```

## Best Practices

### 1. Use Resource Controllers

```bash
# Good ✅
php neo make:controller ProductController --resource --model=Product

# Provides standard CRUD structure
```

### 2. Follow RESTful Conventions

Standard method names:
- `index()` - List resources
- `create()` - Show create form
- `store()` - Save new resource
- `show($id)` - Display resource
- `edit($id)` - Show edit form
- `update($id)` - Update resource
- `destroy($id)` - Delete resource

### 3. Keep Controllers Thin

```php
// Good ✅ - Delegate to services
public function store(Request $request)
{
    $validated = $request->validate([...]);
    $product = $this->productService->create($validated);
    return redirect("/products/{$product->id}");
}

// Bad ❌ - Too much logic in controller
public function store(Request $request)
{
    // 50 lines of business logic...
}
```

### 4. Use Dependency Injection

```php
// Good ✅
public function __construct(
    private ProductService $productService,
    private Cache $cache
) {}

// Bad ❌
public function index()
{
    $service = new ProductService();
    $cache = new Cache();
}
```

### 5. Validate All Input

```php
// Good ✅
$validated = $request->validate([
    'name' => 'required|max:255',
    'price' => 'required|numeric|min:0',
]);

// Bad ❌
$product = Product::create($request->all());
```

## Next Steps

- [Model Generator](model.md)
- [Route Generation](../routing.md)
- [View Templates](../views.md)
- [Form Generator](form.md)
