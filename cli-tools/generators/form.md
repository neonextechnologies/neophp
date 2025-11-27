# Form Generator

The form generator creates HTML forms automatically from your model's metadata, including validation rules and field configurations.

## Basic Usage

```bash
# Generate form from model
php neo make:form Product

# Generate form with custom name
php neo make:form ProductForm --model=Product

# Generate form component
php neo make:form ProductForm --component
```

## Command Syntax

```bash
php neo make:form [name] [options]
```

### Arguments

- `name` - Model name or form name

### Options

- `-m, --model=` - Associate with a model
- `-c, --component` - Generate as reusable component
- `--blade` - Generate Blade template
- `--view` - Generate view file
- `--force` - Overwrite existing file

## Automatic Form Generation

When you have a model with metadata:

```php
#[Table('products')]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Validation('required|max:255')]
    #[FormField(type: 'text', label: 'Product Name', placeholder: 'Enter product name')]
    public string $name;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validation('required|numeric|min:0')]
    #[FormField(type: 'number', label: 'Price', step: '0.01')]
    public float $price;
    
    #[Field(type: 'text', nullable: true)]
    #[FormField(type: 'textarea', label: 'Description', rows: 5)]
    public ?string $description;
    
    #[Field(type: 'boolean', default: true)]
    #[FormField(type: 'checkbox', label: 'In Stock')]
    public bool $in_stock;
}
```

Generate the form:

```bash
php neo make:form Product
```

## Generated Form Output

Creates `views/forms/product.php`:

```php
<form method="POST" action="<?= $action ?? '/products' ?>" class="product-form">
    <?= csrf_field() ?>
    
    <?php if (isset($product)): ?>
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="id" value="<?= $product->id ?>">
    <?php endif; ?>
    
    <!-- Name Field -->
    <div class="form-group">
        <label for="name">Product Name *</label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control <?= $errors->has('name') ? 'is-invalid' : '' ?>"
            value="<?= old('name', $product->name ?? '') ?>"
            placeholder="Enter product name"
            required
            maxlength="255"
        >
        <?php if ($errors->has('name')): ?>
            <div class="invalid-feedback">
                <?= $errors->first('name') ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Price Field -->
    <div class="form-group">
        <label for="price">Price *</label>
        <input 
            type="number" 
            id="price" 
            name="price" 
            class="form-control <?= $errors->has('price') ? 'is-invalid' : '' ?>"
            value="<?= old('price', $product->price ?? '') ?>"
            step="0.01"
            min="0"
            required
        >
        <?php if ($errors->has('price')): ?>
            <div class="invalid-feedback">
                <?= $errors->first('price') ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Description Field -->
    <div class="form-group">
        <label for="description">Description</label>
        <textarea 
            id="description" 
            name="description" 
            class="form-control <?= $errors->has('description') ? 'is-invalid' : '' ?>"
            rows="5"
        ><?= old('description', $product->description ?? '') ?></textarea>
        <?php if ($errors->has('description')): ?>
            <div class="invalid-feedback">
                <?= $errors->first('description') ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- In Stock Field -->
    <div class="form-group form-check">
        <input 
            type="checkbox" 
            id="in_stock" 
            name="in_stock" 
            class="form-check-input"
            value="1"
            <?= old('in_stock', $product->in_stock ?? true) ? 'checked' : '' ?>
        >
        <label for="in_stock" class="form-check-label">In Stock</label>
    </div>
    
    <!-- Submit Button -->
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?= isset($product) ? 'Update' : 'Create' ?> Product
        </button>
        <a href="/products" class="btn btn-secondary">Cancel</a>
    </div>
</form>
```

## Programmatic Form Generation

In your controller:

```php
use NeoPhp\Form\FormGenerator;

class ProductController extends Controller
{
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
}
```

In your view:

```php
<!-- views/products/create.php -->
<h1>Create Product</h1>

<?= $form->render() ?>
```

## Form Configuration

### Customize Form Attributes

```php
$form = FormGenerator::fromModel(Product::class)
    ->action('/products')
    ->method('POST')
    ->class('product-form')
    ->id('create-product')
    ->enctype('multipart/form-data');
```

### Add Custom Fields

```php
$form = FormGenerator::fromModel(Product::class)
    ->addField('custom_field', [
        'type' => 'text',
        'label' => 'Custom Field',
        'required' => true
    ]);
```

### Remove Fields

```php
$form = FormGenerator::fromModel(Product::class)
    ->removeFields(['created_at', 'updated_at']);
```

### Reorder Fields

```php
$form = FormGenerator::fromModel(Product::class)
    ->setFieldOrder(['name', 'sku', 'price', 'description']);
```

## Field Types

### Text Input

```php
#[FormField(type: 'text', label: 'Name', placeholder: 'Enter name')]
public string $name;
```

### Email Input

```php
#[FormField(type: 'email', label: 'Email Address')]
public string $email;
```

### Password Input

```php
#[FormField(type: 'password', label: 'Password', autocomplete: 'new-password')]
public string $password;
```

### Number Input

```php
#[FormField(type: 'number', label: 'Age', min: 18, max: 100)]
public int $age;
```

### Textarea

```php
#[FormField(type: 'textarea', label: 'Description', rows: 5, cols: 50)]
public string $description;
```

### Select Dropdown

```php
#[FormField(
    type: 'select',
    label: 'Category',
    options: [
        1 => 'Electronics',
        2 => 'Clothing',
        3 => 'Books'
    ]
)]
public int $category_id;
```

### Radio Buttons

```php
#[FormField(
    type: 'radio',
    label: 'Status',
    options: [
        'active' => 'Active',
        'inactive' => 'Inactive'
    ]
)]
public string $status;
```

### Checkbox

```php
#[FormField(type: 'checkbox', label: 'Subscribe to newsletter')]
public bool $newsletter;
```

### Date Picker

```php
#[FormField(type: 'date', label: 'Birth Date', min: '1900-01-01', max: '2024-12-31')]
public string $birth_date;
```

### Datetime Picker

```php
#[FormField(type: 'datetime', label: 'Published At')]
public string $published_at;
```

### File Upload

```php
#[FormField(
    type: 'file',
    label: 'Profile Picture',
    accept: 'image/*',
    maxSize: '2M'
)]
public string $avatar;
```

### Multiple File Upload

```php
#[FormField(
    type: 'file',
    label: 'Documents',
    multiple: true,
    accept: '.pdf,.doc,.docx'
)]
public array $documents;
```

## Advanced Examples

### Complete E-commerce Product Form

```bash
php neo make:form Product --view
```

Generates comprehensive form:

```php
<form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div class="row">
        <!-- Basic Information -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <!-- SKU -->
                    <div class="form-group">
                        <label for="sku">SKU *</label>
                        <input type="text" name="sku" class="form-control" required>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control wysiwyg" rows="10"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Pricing -->
            <div class="card mb-3">
                <div class="card-header">Pricing</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Price *</label>
                                <input type="number" name="price" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cost">Cost</label>
                                <input type="number" name="cost" class="form-control" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sale_price">Sale Price</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01">
                    </div>
                </div>
            </div>
            
            <!-- Inventory -->
            <div class="card mb-3">
                <div class="card-header">Inventory</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity</label>
                                <input type="number" name="stock_quantity" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="in_stock" class="form-check-input" checked>
                                <label class="form-check-label">In Stock</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status -->
            <div class="card mb-3">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Category -->
            <div class="card mb-3">
                <div class="card-header">Category</div>
                <div class="card-body">
                    <select name="category_id" class="form-control">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->id ?>"><?= $category->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Featured Image -->
            <div class="card mb-3">
                <div class="card-header">Featured Image</div>
                <div class="card-body">
                    <input type="file" name="featured_image" accept="image/*">
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Product</button>
        <a href="/products" class="btn btn-secondary">Cancel</a>
    </div>
</form>
```

### Form with JavaScript Validation

```bash
php neo make:form Product --with-js
```

Includes JavaScript validation:

```html
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.product-form');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate name
        const name = form.querySelector('[name="name"]');
        if (!name.value.trim()) {
            showError(name, 'Name is required');
            isValid = false;
        }
        
        // Validate price
        const price = form.querySelector('[name="price"]');
        if (!price.value || parseFloat(price.value) < 0) {
            showError(price, 'Please enter a valid price');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    function showError(field, message) {
        field.classList.add('is-invalid');
        const feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }
    }
});
</script>
```

## Form Themes

### Bootstrap 5

```bash
php neo make:form Product --theme=bootstrap5
```

### Tailwind CSS

```bash
php neo make:form Product --theme=tailwind
```

### Material Design

```bash
php neo make:form Product --theme=material
```

## Form Components

Generate reusable form components:

```bash
php neo make:form ProductForm --component
```

Creates `components/forms/ProductForm.php`:

```php
<?php

namespace App\Components\Forms;

use NeoPhp\Form\FormComponent;
use App\Models\Product;

class ProductForm extends FormComponent
{
    protected string $model = Product::class;
    
    public function render(): string
    {
        return $this->generator
            ->action($this->action)
            ->method($this->method)
            ->render();
    }
    
    public function validate(array $data): bool
    {
        return $this->validator->validate($data);
    }
}
```

Use in views:

```php
<?php
use App\Components\Forms\ProductForm;

$form = new ProductForm($product ?? null);
echo $form->render();
?>
```

## Best Practices

### 1. Use Model Metadata

```php
// Good ✅ - Metadata drives everything
#[FormField(type: 'text', label: 'Name')]
#[Validation('required|max:255')]
public string $name;

// Then generate form automatically
php neo make:form Product
```

### 2. Include CSRF Protection

```php
// Always include in forms
<?= csrf_field() ?>
```

### 3. Handle Old Input

```php
// Preserve user input on validation errors
value="<?= old('name', $product->name ?? '') ?>"
```

### 4. Show Validation Errors

```php
<?php if ($errors->has('name')): ?>
    <div class="invalid-feedback">
        <?= $errors->first('name') ?>
    </div>
<?php endif; ?>
```

### 5. Use Appropriate Input Types

```php
// Good ✅
<input type="email" name="email">
<input type="number" name="age">
<input type="date" name="birth_date">

// Bad ❌
<input type="text" name="email">
<input type="text" name="age">
```

## Next Steps

- [View Generator](view.md)
- [CRUD Generator](crud.md)
- [Form Validation](../metadata/validation.md)
- [Form Field Attributes](../metadata/form-generation.md)
