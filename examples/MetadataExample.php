<?php

/**
 * Example: Using Metadata-Driven Architecture
 */

use NeoPhp\Metadata\{Table, Field, HasMany, BelongsTo};
use NeoPhp\Database\Model;

// ===================================
// 1. Define Model with Metadata
// ===================================

#[Table('products')]
class Product extends Model
{
    #[Field('id', 
        type: 'integer', 
        primary: true, 
        autoIncrement: true
    )]
    public int $id;

    #[Field('name',
        type: 'string',
        length: 255,
        nullable: false,
        validation: ['required', 'max:255'],
        label: 'Product Name',
        placeholder: 'Enter product name',
        searchable: true,
        sortable: true
    )]
    public string $name;

    #[Field('sku',
        type: 'string',
        length: 50,
        unique: true,
        validation: ['required', 'unique:products'],
        label: 'SKU',
        searchable: true
    )]
    public string $sku;

    #[Field('price',
        type: 'decimal',
        precision: 10,
        scale: 2,
        unsigned: true,
        min: 0,
        validation: ['required', 'numeric', 'min:0'],
        label: 'Price (THB)',
        inputType: 'number'
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
        nullable: false,
        foreignTable: 'categories',
        foreignKey: 'id',
        onDelete: 'CASCADE',
        validation: ['required', 'exists:categories,id'],
        label: 'Category',
        inputType: 'select'
    )]
    public int $category_id;

    #[Field('brand',
        type: 'string',
        length: 100,
        nullable: true,
        searchable: true,
        filterable: true
    )]
    public ?string $brand;

    #[Field('stock',
        type: 'integer',
        unsigned: true,
        default: 0,
        min: 0,
        validation: ['integer', 'min:0']
    )]
    public int $stock;

    #[Field('image',
        type: 'file',
        mimes: ['jpg', 'jpeg', 'png', 'webp'],
        maxFileSize: 2048, // KB
        validation: ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048']
    )]
    public ?string $image;

    #[Field('gallery',
        type: 'json',
        nullable: true
    )]
    public ?array $gallery;

    #[Field('status',
        type: 'enum',
        enum: [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'draft' => 'Draft'
        ],
        default: 'draft',
        inputType: 'select',
        filterable: true
    )]
    public string $status;

    #[Field('featured',
        type: 'boolean',
        default: false,
        inputType: 'checkbox',
        filterable: true
    )]
    public bool $featured;

    #[BelongsTo(model: Category::class, foreignKey: 'category_id')]
    public function category() {}

    #[HasMany(model: Review::class, foreignKey: 'product_id')]
    public function reviews() {}
}

// ===================================
// 2. Use Metadata in Controller
// ===================================

class ProductController
{
    protected $metadata;
    protected $formBuilder;

    public function __construct()
    {
        $this->metadata = app(\NeoPhp\Metadata\MetadataRepository::class);
        $this->formBuilder = app(\NeoPhp\Forms\FormBuilder::class);
    }

    /**
     * Show create form (Auto-generated from metadata)
     */
    public function create()
    {
        $form = $this->formBuilder->make(Product::class, [
            'action' => '/products',
            'method' => 'POST'
        ]);

        return view('products.create', compact('form'));
    }

    /**
     * Store product (Auto-validation from metadata)
     */
    public function store()
    {
        // Get validation rules automatically from metadata
        $rules = $this->metadata->getValidationRules(Product::class);
        
        // Validate
        $validator = validator($_POST + $_FILES, $rules);

        if ($validator->fails()) {
            // Show form with errors
            $form = $this->formBuilder->make(Product::class, [
                'action' => '/products',
                'method' => 'POST',
                'values' => $_POST,
                'errors' => $validator->errors()
            ]);

            return view('products.create', compact('form'));
        }

        // Handle file upload
        if (isset($_FILES['image'])) {
            $imagePath = storage()->putFile('products', $_FILES['image']);
            $validated = $validator->validated();
            $validated['image'] = $imagePath;
        } else {
            $validated = $validator->validated();
        }

        // Create product
        $product = Product::create($validated);

        // Trigger hook
        do_action('product.created', $product);

        return redirect('/products')->with('success', 'Product created successfully');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return redirect('/products')->with('error', 'Product not found');
        }

        $form = $this->formBuilder->make(Product::class, [
            'action' => "/products/{$id}",
            'method' => 'PUT',
            'values' => $product->toArray()
        ]);

        return view('products.edit', compact('form', 'product'));
    }

    /**
     * List products with metadata
     */
    public function index()
    {
        $metadata = $this->metadata->getModelMetadata(Product::class);
        $products = Product::paginate(25);

        // Get searchable fields from metadata
        $searchableFields = array_filter(
            $metadata['fields'],
            fn($field) => $field['searchable']
        );

        // Get filterable fields
        $filterableFields = array_filter(
            $metadata['fields'],
            fn($field) => $field['filterable']
        );

        return view('products.index', [
            'products' => $products,
            'metadata' => $metadata,
            'searchableFields' => $searchableFields,
            'filterableFields' => $filterableFields
        ]);
    }
}

// ===================================
// 3. View (Blade Template)
// ===================================

/*
<!-- resources/views/products/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Product</h1>
    
    <!-- Auto-generated form from metadata -->
    {!! $form !!}
</div>
@endsection
*/

/*
<!-- resources/views/products/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Products</h1>
    
    <!-- Dynamic search form based on searchable fields -->
    <form method="GET" action="/products">
        @foreach($searchableFields as $field)
            <input type="text" name="search_{{ $field['name'] }}" placeholder="Search {{ $field['label'] }}">
        @endforeach
        <button type="submit">Search</button>
    </form>
    
    <!-- Dynamic filters based on filterable fields -->
    <form method="GET" action="/products">
        @foreach($filterableFields as $field)
            @if($field['inputType'] === 'select')
                <select name="filter_{{ $field['name'] }}">
                    <option value="">All {{ $field['label'] }}</option>
                    @foreach($field['enum'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        @endforeach
        <button type="submit">Filter</button>
    </form>
    
    <!-- Products table -->
    <table class="table">
        <thead>
            <tr>
                @foreach($metadata['fields'] as $field)
                    @if(!$field['hidden'])
                        <th>{{ $field['label'] }}</th>
                    @endif
                @endforeach
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products->items() as $product)
                <tr>
                    @foreach($metadata['fields'] as $fieldName => $field)
                        @if(!$field['hidden'])
                            <td>{{ $product->$fieldName }}</td>
                        @endif
                    @endforeach
                    <td>
                        <a href="/products/{{ $product->id }}/edit">Edit</a>
                        <form method="POST" action="/products/{{ $product->id }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Pagination -->
    {!! $products->links() !!}
</div>
@endsection
*/

// ===================================
// 4. Using Hooks
// ===================================

// Register action hook (in plugin or bootstrap)
hook_action('product.created', function($product) {
    // Send notification
    logger()->info("Product created: {$product->name}");
    
    // Update search index
    // SearchService::index($product);
    
    // Trigger email
    // mail()->send(['admin@example.com'], 'New Product', "Product {$product->name} created");
});

// Register filter hook
hook_filter('product.price', function($price, $product) {
    // Apply discount for featured products
    if ($product->featured) {
        return $price * 0.9; // 10% discount
    }
    return $price;
}, 10, 2);

// Use filter in code
$finalPrice = apply_filters('product.price', $product->price, $product);

// ===================================
// 5. Generic CRUD Controller
// ===================================

class GenericController
{
    protected $metadata;
    protected $formBuilder;

    public function __construct()
    {
        $this->metadata = metadata();
        $this->formBuilder = form();
    }

    /**
     * Generic index for any model
     */
    public function index(string $modelClass)
    {
        $metadata = $this->metadata->getModelMetadata($modelClass);
        $items = $modelClass::paginate(25);
        
        return view('admin.generic.index', [
            'items' => $items,
            'metadata' => $metadata,
            'model' => $modelClass
        ]);
    }

    /**
     * Generic create for any model
     */
    public function create(string $modelClass)
    {
        $form = $this->formBuilder->make($modelClass, [
            'action' => "/admin/{$modelClass}",
            'method' => 'POST'
        ]);
        
        return view('admin.generic.create', [
            'form' => $form,
            'model' => $modelClass
        ]);
    }

    /**
     * Generic store for any model
     */
    public function store(string $modelClass)
    {
        $rules = $this->metadata->getValidationRules($modelClass);
        $validator = validator($_POST + $_FILES, $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $modelClass::create($validator->validated());
        
        return redirect("/admin/{$modelClass}")->with('success', 'Created successfully');
    }
}

// Routes for generic CRUD
// Route::get('/admin/{model}', [GenericController::class, 'index']);
// Route::get('/admin/{model}/create', [GenericController::class, 'create']);
// Route::post('/admin/{model}', [GenericController::class, 'store']);
// ...
