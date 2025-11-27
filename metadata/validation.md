# Validation

Define validation rules using metadata attributes.

## Basic Validation

```php
use NeoPhp\Metadata\Attributes\{Field, Validate};

class User extends Model
{
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'email', 'unique:users,email'])]
    public string $email;
}
```

## Validation Rules

### Required

```php
#[Validate(['required'])]
public string $name;

// Required if another field has value
#[Validate(['required_if:status,active'])]
public ?string $reason;

// Required unless another field has value
#[Validate(['required_unless:role,guest'])]
public ?string $password;

// Required with another field
#[Validate(['required_with:phone'])]
public ?string $country_code;
```

### String Rules

```php
// String type
#[Validate(['string'])]
public string $name;

// Min length
#[Validate(['string', 'min:3'])]
public string $username;

// Max length
#[Validate(['string', 'max:255'])]
public string $email;

// Between
#[Validate(['string', 'between:3,50'])]
public string $username;

// Exact length
#[Validate(['string', 'size:10'])]
public string $phone;

// Alpha
#[Validate(['alpha'])]
public string $first_name;

// Alpha numeric
#[Validate(['alpha_num'])]
public string $username;

// Alpha dash (letters, numbers, dashes, underscores)
#[Validate(['alpha_dash'])]
public string $slug;
```

### Numeric Rules

```php
// Numeric
#[Validate(['numeric'])]
public float $price;

// Integer
#[Validate(['integer'])]
public int $age;

// Min value
#[Validate(['integer', 'min:18'])]
public int $age;

// Max value
#[Validate(['integer', 'max:150'])]
public int $age;

// Between
#[Validate(['numeric', 'between:0,100'])]
public float $discount_percentage;

// Digits (exact length)
#[Validate(['digits:4'])]
public string $pin;

// Digits between
#[Validate(['digits_between:10,12'])]
public string $phone;
```

### Email and URL

```php
// Email
#[Validate(['required', 'email'])]
public string $email;

// URL
#[Validate(['url'])]
public string $website;

// Active URL (DNS check)
#[Validate(['active_url'])]
public string $website;
```

### Date Rules

```php
// Date
#[Validate(['date'])]
public string $birth_date;

// Date format
#[Validate(['date_format:Y-m-d'])]
public string $birth_date;

// After date
#[Validate(['after:today'])]
public string $expiry_date;

// After or equal
#[Validate(['after_or_equal:today'])]
public string $start_date;

// Before date
#[Validate(['before:2024-12-31'])]
public string $birth_date;

// Before or equal
#[Validate(['before_or_equal:today'])]
public string $birth_date;
```

### Unique and Exists

```php
// Unique (new records)
#[Validate(['unique:users,email'])]
public string $email;

// Unique except current record (updates)
#[Validate(['unique:users,email,{id}'])]
public string $email;

// Unique with additional conditions
#[Validate(['unique:users,email,NULL,id,tenant_id,{tenant_id}'])]
public string $email;

// Exists in table
#[Validate(['exists:categories,id'])]
public int $category_id;
```

### In and Not In

```php
// In list
#[Validate(['in:pending,active,suspended,cancelled'])]
public string $status;

// Not in list
#[Validate(['not_in:admin,superadmin'])]
public string $username;
```

### Boolean

```php
#[Validate(['boolean'])]
public bool $is_active;

// Accepted (1, "1", true, "true", "yes", "on")
#[Validate(['accepted'])]
public bool $terms_accepted;
```

### Array Rules

```php
// Array
#[Validate(['array'])]
public array $tags;

// Array with min items
#[Validate(['array', 'min:1'])]
public array $tags;

// Array with max items
#[Validate(['array', 'max:10'])]
public array $tags;

// Array values must be in list
#[Validate(['array'], ['tags.*' => 'in:tag1,tag2,tag3'])]
public array $tags;
```

### File Rules

```php
// File
#[Validate(['file'])]
public $document;

// Image
#[Validate(['image'])]
public $photo;

// Mimes
#[Validate(['mimes:jpg,png,gif'])]
public $image;

// Mime types
#[Validate(['mimetypes:image/jpeg,image/png'])]
public $image;

// Max file size (kilobytes)
#[Validate(['max:2048'])]  // 2MB
public $file;

// Image dimensions
#[Validate(['dimensions:min_width=100,min_height=100'])]
public $image;
```

### Regular Expression

```php
// Regex
#[Validate(['regex:/^[A-Z]{2}[0-9]{4}$/'])]
public string $code;

// Not regex
#[Validate(['not_regex:/admin|root/i'])]
public string $username;
```

## Complete Model Examples

### User Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('users')]
#[Timestamps]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100)]
    #[Validate(['required', 'string', 'min:2', 'max:100'])]
    public string $first_name;
    
    #[Field(type: 'string', length: 100)]
    #[Validate(['required', 'string', 'min:2', 'max:100'])]
    public string $last_name;
    
    #[Field(type: 'string', length: 255, unique: true)]
    #[Validate(['required', 'email', 'unique:users,email,{id}'])]
    public string $email;
    
    #[Field(type: 'string', length: 50, unique: true)]
    #[Validate([
        'required',
        'string',
        'min:3',
        'max:50',
        'alpha_dash',
        'unique:users,username,{id}'
    ])]
    public string $username;
    
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'string', 'min:8'])]
    public string $password;
    
    #[Field(type: 'string', length: 20, nullable: true)]
    #[Validate(['nullable', 'string', 'regex:/^[0-9]{10,15}$/'])]
    public ?string $phone;
    
    #[Field(type: 'date', nullable: true)]
    #[Validate(['nullable', 'date', 'before:today'])]
    public ?string $birth_date;
    
    #[Field(type: 'enum', allowed: ['male', 'female', 'other'], nullable: true)]
    #[Validate(['nullable', 'in:male,female,other'])]
    public ?string $gender;
    
    #[Field(type: 'string', length: 255, nullable: true)]
    #[Validate(['nullable', 'url'])]
    public ?string $website;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Validate(['nullable', 'integer', 'min:18', 'max:150'])]
    public ?int $age;
    
    #[Field(type: 'boolean', default: false)]
    #[Validate(['boolean'])]
    public bool $is_verified;
    
    #[Field(type: 'boolean', default: true)]
    #[Validate(['boolean'])]
    public bool $is_active;
}
```

### Product Model

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
    #[Validate(['required', 'string', 'min:3', 'max:255'])]
    public string $name;
    
    #[Field(type: 'string', length: 100, unique: true)]
    #[Validate([
        'required',
        'string',
        'max:100',
        'alpha_dash',
        'unique:products,sku,{id}'
    ])]
    public string $sku;
    
    #[Field(type: 'text', nullable: true)]
    #[Validate(['nullable', 'string', 'max:5000'])]
    public ?string $description;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validate(['required', 'numeric', 'min:0', 'max:999999.99'])]
    public float $price;
    
    #[Field(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Validate([
        'nullable',
        'numeric',
        'min:0',
        'lt:price'  // Less than price
    ])]
    public ?float $sale_price;
    
    #[Field(type: 'integer', unsigned: true, default: 0)]
    #[Validate(['required', 'integer', 'min:0'])]
    public int $stock_quantity;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Validate(['required', 'integer', 'exists:categories,id'])]
    public int $category_id;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Validate(['nullable', 'integer', 'exists:brands,id'])]
    public ?int $brand_id;
    
    #[Field(
        type: 'enum',
        allowed: ['draft', 'active', 'out_of_stock', 'discontinued'],
        default: 'draft'
    )]
    #[Validate(['required', 'in:draft,active,out_of_stock,discontinued'])]
    public string $status;
    
    #[Field(type: 'float', precision: 3, scale: 2, default: 0)]
    #[Validate(['required', 'numeric', 'min:0', 'max:999.99'])]
    public float $weight_kg;
    
    #[Field(type: 'json', nullable: true)]
    #[Validate(['nullable', 'array'])]
    public ?array $specifications;
}
```

### Post Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('posts')]
#[Timestamps]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'string', 'min:3', 'max:255'])]
    public string $title;
    
    #[Field(type: 'string', length: 255, unique: true)]
    #[Validate([
        'required',
        'string',
        'max:255',
        'alpha_dash',
        'unique:posts,slug,{id}'
    ])]
    public string $slug;
    
    #[Field(type: 'text', nullable: true)]
    #[Validate(['nullable', 'string', 'max:500'])]
    public ?string $excerpt;
    
    #[Field(type: 'longtext')]
    #[Validate(['required', 'string', 'min:100'])]
    public string $content;
    
    #[Field(type: 'string', length: 255, nullable: true)]
    #[Validate(['nullable', 'string', 'url'])]
    public ?string $featured_image;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Validate(['required', 'integer', 'exists:users,id'])]
    public int $user_id;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Validate(['nullable', 'integer', 'exists:categories,id'])]
    public ?int $category_id;
    
    #[Field(
        type: 'enum',
        allowed: ['draft', 'published', 'archived'],
        default: 'draft'
    )]
    #[Validate(['required', 'in:draft,published,archived'])]
    public string $status;
    
    #[Field(type: 'datetime', nullable: true)]
    #[Validate(['nullable', 'date', 'after_or_equal:today'])]
    public ?string $published_at;
    
    #[Field(type: 'boolean', default: true)]
    #[Validate(['boolean'])]
    public bool $allow_comments;
}
```

## Using Validation

### Automatic Validation

```php
use NeoPhp\Validation\Validator;

// Get validation rules from model
$rules = Validator::fromModel(User::class);

// Validate data
$validator = new Validator($_POST, $rules);

if ($validator->fails()) {
    $errors = $validator->errors();
    // Handle errors
} else {
    $validated = $validator->validated();
    // Use validated data
}
```

Generated rules for User model:

```php
[
    'first_name' => ['required', 'string', 'min:2', 'max:100'],
    'last_name' => ['required', 'string', 'min:2', 'max:100'],
    'email' => ['required', 'email', 'unique:users,email'],
    'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
    'password' => ['required', 'string', 'min:8'],
    'phone' => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
    'birth_date' => ['nullable', 'date', 'before:today'],
    'gender' => ['nullable', 'in:male,female,other'],
    'website' => ['nullable', 'url'],
    'age' => ['nullable', 'integer', 'min:18', 'max:150'],
    'is_verified' => ['boolean'],
    'is_active' => ['boolean'],
]
```

### Manual Validation

```php
$validator = new Validator($_POST, [
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:users,email'],
    'age' => ['required', 'integer', 'min:18'],
]);

if ($validator->fails()) {
    foreach ($validator->errors() as $field => $messages) {
        foreach ($messages as $message) {
            echo "$field: $message<br>";
        }
    }
}
```

### In Controllers

```php
<?php

namespace App\Controllers;

use App\Models\User;
use NeoPhp\Foundation\Controller;
use NeoPhp\Validation\Validator;

class UserController extends Controller
{
    public function store()
    {
        // Get rules from model metadata
        $rules = Validator::fromModel(User::class);
        
        // Validate request
        $validator = new Validator($this->request->all(), $rules);
        
        if ($validator->fails()) {
            return $this->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Create user with validated data
        $user = User::create($validator->validated());
        
        return $this->json([
            'success' => true,
            'data' => $user
        ], 201);
    }
    
    public function update(int $id)
    {
        $user = User::findOrFail($id);
        
        // Get rules from model metadata
        $rules = Validator::fromModel(User::class, $id);
        
        // Validate
        $validator = new Validator($this->request->all(), $rules);
        
        if ($validator->fails()) {
            return $this->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Update user
        $user->update($validator->validated());
        
        return $this->json([
            'success' => true,
            'data' => $user
        ]);
    }
}
```

## Custom Validation Rules

### Creating Custom Rules

```php
<?php

namespace App\Validation\Rules;

use NeoPhp\Validation\Rule;

class Uppercase implements Rule
{
    public function passes(string $attribute, $value): bool
    {
        return $value === strtoupper($value);
    }
    
    public function message(): string
    {
        return 'The :attribute must be uppercase.';
    }
}
```

### Using Custom Rules

```php
use App\Validation\Rules\Uppercase;

#[Field(type: 'string', length: 10)]
#[Validate(['required', new Uppercase()])]
public string $country_code;
```

## Conditional Validation

```php
#[Field(type: 'string', length: 255, nullable: true)]
#[Validate([
    'required_if:role,admin',
    'email'
])]
public ?string $admin_email;

#[Field(type: 'integer', nullable: true)]
#[Validate([
    'required_with:card_number',
    'digits:3'
])]
public ?string $cvv;

#[Field(type: 'string', length: 255, nullable: true)]
#[Validate([
    'required_unless:status,guest',
    'string'
])]
public ?string $password;
```

## Nested Array Validation

```php
#[Field(type: 'json')]
#[Validate(['required', 'array'], [
    'items.*.name' => ['required', 'string'],
    'items.*.quantity' => ['required', 'integer', 'min:1'],
    'items.*.price' => ['required', 'numeric', 'min:0'],
])]
public array $items;
```

Usage:

```php
$validator = new Validator($_POST, [
    'items' => ['required', 'array'],
    'items.*.name' => ['required', 'string'],
    'items.*.quantity' => ['required', 'integer', 'min:1'],
    'items.*.price' => ['required', 'numeric', 'min:0'],
]);
```

## Best Practices

### 1. Always Validate User Input

```php
// Good ✅
#[Field(type: 'string', length: 255)]
#[Validate(['required', 'email', 'unique:users,email'])]
public string $email;

// Bad ❌
#[Field(type: 'string', length: 255)]
public string $email;  // No validation
```

### 2. Use Appropriate Rules

```php
// Good ✅
#[Field(type: 'decimal', precision: 10, scale: 2)]
#[Validate(['required', 'numeric', 'min:0'])]
public float $price;

// Bad ❌
#[Field(type: 'decimal', precision: 10, scale: 2)]
#[Validate(['required', 'string'])]  // Wrong type
public float $price;
```

### 3. Validate Relationships

```php
// Good ✅
#[Field(type: 'integer', unsigned: true)]
#[Validate(['required', 'integer', 'exists:categories,id'])]
public int $category_id;

// Bad ❌
#[Field(type: 'integer', unsigned: true)]
#[Validate(['required', 'integer'])]  // No FK check
public int $category_id;
```

### 4. Handle Unique Validation on Updates

```php
// Good ✅
#[Field(type: 'string', length: 255, unique: true)]
#[Validate(['required', 'email', 'unique:users,email,{id}'])]
public string $email;

// Uses {id} placeholder for current record
```

### 5. Provide Clear Error Messages

```php
$validator = new Validator($_POST, $rules, [
    'email.required' => 'Please provide your email address.',
    'email.email' => 'Please provide a valid email address.',
    'email.unique' => 'This email is already registered.',
]);
```

## Next Steps

- [Form Generation](form-generation.md)
- [Controllers](../cli/generators/controller.md)
- [Migrations](../database/migrations.md)
- [Query Builder](../database/query-builder.md)
