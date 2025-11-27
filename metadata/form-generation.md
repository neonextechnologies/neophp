# Form Generation

Automatically generate forms from model metadata.

## Basic Form Generation

```php
use NeoPhp\Form\FormGenerator;
use App\Models\User;

$form = FormGenerator::fromModel(User::class);
echo $form->render();
```

This generates a complete HTML form with all fields from the User model.

## Form Components

### Field Types

NeoPhp automatically generates appropriate input types based on field metadata:

| Metadata Type | HTML Input Type |
|---------------|-----------------|
| `string` | `<input type="text">` |
| `text` | `<textarea>` |
| `integer` | `<input type="number">` |
| `decimal/float` | `<input type="number" step="0.01">` |
| `boolean` | `<input type="checkbox">` |
| `date` | `<input type="date">` |
| `datetime` | `<input type="datetime-local">` |
| `time` | `<input type="time">` |
| `email` | `<input type="email">` |
| `url` | `<input type="url">` |
| `password` | `<input type="password">` |
| `enum` | `<select>` |
| `json/array` | `<textarea>` |

### Example Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('users')]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100)]
    #[Validate(['required', 'string', 'min:2', 'max:100'])]
    #[FormField(label: 'First Name', placeholder: 'Enter first name')]
    public string $first_name;
    
    #[Field(type: 'string', length: 255, unique: true)]
    #[Validate(['required', 'email'])]
    #[FormField(label: 'Email Address', type: 'email')]
    public string $email;
    
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'string', 'min:8'])]
    #[FormField(label: 'Password', type: 'password')]
    public string $password;
    
    #[Field(type: 'enum', allowed: ['male', 'female', 'other'], nullable: true)]
    #[Validate(['nullable', 'in:male,female,other'])]
    #[FormField(label: 'Gender', type: 'select', options: [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other'
    ])]
    public ?string $gender;
    
    #[Field(type: 'boolean', default: false)]
    #[Validate(['boolean'])]
    #[FormField(label: 'Is Active', type: 'checkbox')]
    public bool $is_active;
}
```

## FormField Attribute

Control form field rendering with `#[FormField]`:

```php
#[FormField(
    label: 'Field Label',
    type: 'text',
    placeholder: 'Enter value',
    help: 'Help text',
    class: 'form-control',
    attributes: ['data-foo' => 'bar'],
    hidden: false,
    disabled: false,
    readonly: false,
    order: 10
)]
public string $field;
```

### Available Options

| Option | Type | Description |
|--------|------|-------------|
| `label` | string | Field label |
| `type` | string | Input type (text, email, select, etc.) |
| `placeholder` | string | Placeholder text |
| `help` | string | Help text below field |
| `class` | string | CSS class |
| `attributes` | array | Additional HTML attributes |
| `options` | array | Select/radio options |
| `hidden` | bool | Hide field |
| `disabled` | bool | Disable field |
| `readonly` | bool | Make readonly |
| `order` | int | Field order (lower = earlier) |

## Form Builder

### Create Form

```php
use NeoPhp\Form\FormBuilder;
use App\Models\User;

$form = new FormBuilder(User::class);

// Set form attributes
$form->setAction('/users')
     ->setMethod('POST')
     ->setClass('user-form')
     ->setId('create-user-form');

// Render
echo $form->render();
```

### Customize Fields

```php
$form = new FormBuilder(User::class);

// Modify specific field
$form->field('email')
     ->setLabel('Email Address')
     ->setPlaceholder('you@example.com')
     ->setHelp('We will never share your email.');

// Hide field
$form->field('password')->hide();

// Add custom field
$form->addField('confirm_password', [
    'type' => 'password',
    'label' => 'Confirm Password',
    'placeholder' => 'Re-enter password',
    'validate' => ['required', 'same:password']
]);

echo $form->render();
```

### Field Ordering

```php
$form = new FormBuilder(User::class);

// Reorder fields
$form->field('email')->setOrder(1);
$form->field('first_name')->setOrder(2);
$form->field('last_name')->setOrder(3);

echo $form->render();
```

## Complete Examples

### User Registration Form

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('users')]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100)]
    #[Validate(['required', 'string', 'min:2', 'max:100'])]
    #[FormField(
        label: 'First Name',
        placeholder: 'John',
        order: 1
    )]
    public string $first_name;
    
    #[Field(type: 'string', length: 100)]
    #[Validate(['required', 'string', 'min:2', 'max:100'])]
    #[FormField(
        label: 'Last Name',
        placeholder: 'Doe',
        order: 2
    )]
    public string $last_name;
    
    #[Field(type: 'string', length: 255, unique: true)]
    #[Validate(['required', 'email', 'unique:users,email'])]
    #[FormField(
        label: 'Email Address',
        type: 'email',
        placeholder: 'john.doe@example.com',
        help: 'We will never share your email.',
        order: 3
    )]
    public string $email;
    
    #[Field(type: 'string', length: 50, unique: true)]
    #[Validate(['required', 'string', 'min:3', 'alpha_dash'])]
    #[FormField(
        label: 'Username',
        placeholder: 'johndoe',
        order: 4
    )]
    public string $username;
    
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'string', 'min:8'])]
    #[FormField(
        label: 'Password',
        type: 'password',
        help: 'Must be at least 8 characters.',
        order: 5
    )]
    public string $password;
    
    #[Field(type: 'date', nullable: true)]
    #[Validate(['nullable', 'date', 'before:today'])]
    #[FormField(
        label: 'Date of Birth',
        type: 'date',
        order: 6
    )]
    public ?string $birth_date;
    
    #[Field(type: 'enum', allowed: ['male', 'female', 'other'], nullable: true)]
    #[Validate(['nullable', 'in:male,female,other'])]
    #[FormField(
        label: 'Gender',
        type: 'select',
        options: [
            '' => '- Select -',
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other'
        ],
        order: 7
    )]
    public ?string $gender;
    
    #[Field(type: 'boolean', default: false)]
    #[Validate(['accepted'])]
    #[FormField(
        label: 'I agree to the Terms and Conditions',
        type: 'checkbox',
        order: 8
    )]
    public bool $terms_accepted;
}
```

Controller:

```php
<?php

namespace App\Controllers;

use App\Models\User;
use NeoPhp\Foundation\Controller;
use NeoPhp\Form\FormBuilder;

class RegisterController extends Controller
{
    public function showForm()
    {
        $form = new FormBuilder(User::class);
        
        $form->setAction('/register')
             ->setMethod('POST')
             ->setClass('registration-form');
        
        // Add confirm password field
        $form->addField('confirm_password', [
            'type' => 'password',
            'label' => 'Confirm Password',
            'validate' => ['required', 'same:password'],
            'order' => 6
        ]);
        
        return $this->view('register', [
            'form' => $form
        ]);
    }
}
```

View:

```php
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Create Account</h1>
        <?= $form->render() ?>
    </div>
</body>
</html>
```

### Product Form

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('products')]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'string', 'min:3', 'max:255'])]
    #[FormField(
        label: 'Product Name',
        placeholder: 'Enter product name',
        order: 1
    )]
    public string $name;
    
    #[Field(type: 'string', length: 100, unique: true)]
    #[Validate(['required', 'string', 'unique:products,sku'])]
    #[FormField(
        label: 'SKU',
        placeholder: 'PROD-001',
        help: 'Stock Keeping Unit (unique)',
        order: 2
    )]
    public string $sku;
    
    #[Field(type: 'text', nullable: true)]
    #[Validate(['nullable', 'string', 'max:5000'])]
    #[FormField(
        label: 'Description',
        type: 'textarea',
        attributes: ['rows' => 5],
        order: 3
    )]
    public ?string $description;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validate(['required', 'numeric', 'min:0'])]
    #[FormField(
        label: 'Price',
        type: 'number',
        attributes: ['step' => '0.01', 'min' => '0'],
        order: 4
    )]
    public float $price;
    
    #[Field(type: 'integer', unsigned: true, default: 0)]
    #[Validate(['required', 'integer', 'min:0'])]
    #[FormField(
        label: 'Stock Quantity',
        type: 'number',
        attributes: ['min' => '0'],
        order: 5
    )]
    public int $stock_quantity;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Validate(['required', 'integer', 'exists:categories,id'])]
    #[FormField(
        label: 'Category',
        type: 'select',
        order: 6
    )]
    public int $category_id;
    
    #[Field(type: 'enum', allowed: ['draft', 'active', 'discontinued'])]
    #[Validate(['required', 'in:draft,active,discontinued'])]
    #[FormField(
        label: 'Status',
        type: 'select',
        options: [
            'draft' => 'Draft',
            'active' => 'Active',
            'discontinued' => 'Discontinued'
        ],
        order: 7
    )]
    public string $status;
    
    #[Field(type: 'boolean', default: false)]
    #[Validate(['boolean'])]
    #[FormField(
        label: 'Featured Product',
        type: 'checkbox',
        order: 8
    )]
    public bool $is_featured;
}
```

Controller:

```php
<?php

namespace App\Controllers;

use App\Models\{Product, Category};
use NeoPhp\Foundation\Controller;
use NeoPhp\Form\FormBuilder;

class ProductController extends Controller
{
    public function create()
    {
        $form = new FormBuilder(Product::class);
        
        // Populate category dropdown
        $categories = Category::all();
        $categoryOptions = ['' => '- Select Category -'];
        foreach ($categories as $category) {
            $categoryOptions[$category->id] = $category->name;
        }
        
        $form->field('category_id')->setOptions($categoryOptions);
        
        $form->setAction('/products')
             ->setMethod('POST');
        
        return $this->view('products/create', [
            'form' => $form
        ]);
    }
    
    public function edit(int $id)
    {
        $product = Product::findOrFail($id);
        
        $form = new FormBuilder(Product::class, $product);
        
        // Populate category dropdown
        $categories = Category::all();
        $categoryOptions = ['' => '- Select Category -'];
        foreach ($categories as $category) {
            $categoryOptions[$category->id] = $category->name;
        }
        
        $form->field('category_id')->setOptions($categoryOptions);
        
        $form->setAction("/products/{$id}")
             ->setMethod('PUT');
        
        return $this->view('products/edit', [
            'form' => $form,
            'product' => $product
        ]);
    }
}
```

### Blog Post Form

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('posts')]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'string', 'min:3', 'max:255'])]
    #[FormField(
        label: 'Title',
        placeholder: 'Enter post title',
        order: 1
    )]
    public string $title;
    
    #[Field(type: 'string', length: 255, unique: true)]
    #[Validate(['required', 'string', 'alpha_dash', 'unique:posts,slug'])]
    #[FormField(
        label: 'Slug',
        placeholder: 'post-slug',
        help: 'URL-friendly version of title',
        order: 2
    )]
    public string $slug;
    
    #[Field(type: 'text', nullable: true)]
    #[Validate(['nullable', 'string', 'max:500'])]
    #[FormField(
        label: 'Excerpt',
        type: 'textarea',
        attributes: ['rows' => 3],
        help: 'Short summary (max 500 characters)',
        order: 3
    )]
    public ?string $excerpt;
    
    #[Field(type: 'longtext')]
    #[Validate(['required', 'string', 'min:100'])]
    #[FormField(
        label: 'Content',
        type: 'textarea',
        attributes: ['rows' => 15, 'class' => 'wysiwyg'],
        order: 4
    )]
    public string $content;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Validate(['nullable', 'integer', 'exists:categories,id'])]
    #[FormField(
        label: 'Category',
        type: 'select',
        order: 5
    )]
    public ?int $category_id;
    
    #[Field(type: 'enum', allowed: ['draft', 'published', 'archived'])]
    #[Validate(['required', 'in:draft,published,archived'])]
    #[FormField(
        label: 'Status',
        type: 'radio',
        options: [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived'
        ],
        order: 6
    )]
    public string $status;
    
    #[Field(type: 'datetime', nullable: true)]
    #[Validate(['nullable', 'date'])]
    #[FormField(
        label: 'Publish Date',
        type: 'datetime-local',
        order: 7
    )]
    public ?string $published_at;
    
    #[Field(type: 'boolean', default: true)]
    #[Validate(['boolean'])]
    #[FormField(
        label: 'Allow Comments',
        type: 'checkbox',
        order: 8
    )]
    public bool $allow_comments;
}
```

## Form Themes

### Bootstrap 5

```php
use NeoPhp\Form\Themes\Bootstrap5Theme;

$form = new FormBuilder(User::class);
$form->setTheme(new Bootstrap5Theme());
echo $form->render();
```

Generated HTML:

```html
<form method="POST" action="/users" class="needs-validation">
    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" name="first_name" id="first_name" 
               class="form-control" placeholder="Enter first name" required>
        <div class="invalid-feedback">Please provide a first name.</div>
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" name="email" id="email" 
               class="form-control" placeholder="you@example.com" required>
        <div class="form-text">We will never share your email.</div>
        <div class="invalid-feedback">Please provide a valid email.</div>
    </div>
    
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Tailwind CSS

```php
use NeoPhp\Form\Themes\TailwindTheme;

$form = new FormBuilder(User::class);
$form->setTheme(new TailwindTheme());
echo $form->render();
```

## CLI Generation

Generate form directly from model:

```bash
php neo make:form User
```

Creates `app/Forms/UserForm.php`:

```php
<?php

namespace App\Forms;

use App\Models\User;
use NeoPhp\Form\FormBuilder;

class UserForm extends FormBuilder
{
    public function __construct(?User $user = null)
    {
        parent::__construct(User::class, $user);
        
        $this->configure();
    }
    
    protected function configure(): void
    {
        $this->setMethod('POST')
             ->setAction('/users');
        
        // Add custom field
        $this->addField('confirm_password', [
            'type' => 'password',
            'label' => 'Confirm Password',
            'validate' => ['required', 'same:password'],
            'order' => 6
        ]);
    }
}
```

Usage:

```php
$form = new UserForm();
echo $form->render();
```

## Best Practices

### 1. Use FormField Attributes

```php
// Good ✅
#[Field(type: 'string', length: 255)]
#[FormField(
    label: 'Email Address',
    type: 'email',
    placeholder: 'you@example.com',
    help: 'We will never share your email.'
)]
public string $email;

// Bad ❌ - No form guidance
#[Field(type: 'string', length: 255)]
public string $email;
```

### 2. Order Fields Logically

```php
// Good ✅
#[FormField(label: 'First Name', order: 1)]
public string $first_name;

#[FormField(label: 'Last Name', order: 2)]
public string $last_name;

#[FormField(label: 'Email', order: 3)]
public string $email;
```

### 3. Populate Select Options

```php
// Good ✅
$categories = Category::all();
$options = ['' => '- Select -'];
foreach ($categories as $category) {
    $options[$category->id] = $category->name;
}
$form->field('category_id')->setOptions($options);
```

### 4. Add Help Text

```php
// Good ✅
#[FormField(
    label: 'Password',
    type: 'password',
    help: 'Must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number.'
)]
public string $password;
```

### 5. Use Themes for Consistency

```php
// Good ✅
$form->setTheme(new Bootstrap5Theme());
```

## Next Steps

- [Validation](validation.md)
- [Controllers](../cli/generators/controller.md)
- [CRUD Generation](../cli/generators/crud.md)
- [Custom Commands](../cli/custom-commands.md)
