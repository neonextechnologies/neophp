# Metadata System - Introduction

The Metadata System is NeoPhp's most powerful feature - it allows you to define your entire database schema, validation rules, relationships, and forms using PHP attributes on your models.

## What is Metadata?

Metadata is "data about data". In NeoPhp, metadata attributes describe:

- **Database Schema**: Table structure, columns, indexes
- **Validation Rules**: Input validation requirements
- **Relationships**: How models relate to each other
- **Form Generation**: Automatic HTML form creation
- **Display Preferences**: How data should be presented

## Why Use Metadata?

### Single Source of Truth

Traditional approach requires multiple files:

```
models/User.php           # Model definition
migrations/create_users   # Database schema
validation/UserRequest    # Validation rules
forms/UserForm           # Form configuration
```

NeoPhp approach - everything in one place:

```php
#[Table('users')]
class User extends Model
{
    #[Field(type: 'string')]
    #[Validation('required|email')]
    #[FormField(type: 'email', label: 'Email Address')]
    public string $email;
}
```

### Benefits

1. **DRY Principle**: Define once, use everywhere
2. **Type Safety**: PHP 8+ attributes with IDE support
3. **Auto-Generation**: Migrations, forms, validation
4. **Consistency**: Schema and validation always match
5. **Maintainability**: Changes in one place

## Metadata Categories

### Table Attributes

Define table-level settings:

```php
#[Table('users')]                    // Table name
#[Timestamps]                        // created_at, updated_at
#[SoftDeletes]                       // deleted_at
class User extends Model {}
```

### Field Attributes

Define column properties:

```php
#[Field(type: 'string', length: 255, unique: true)]
public string $email;

#[Field(type: 'decimal', precision: 10, scale: 2)]
public float $price;
```

### Relationship Attributes

Define model relationships:

```php
#[BelongsTo(User::class)]
public User $author;

#[HasMany(Post::class)]
public array $posts;

#[BelongsToMany(Tag::class)]
public array $tags;
```

### Validation Attributes

Define validation rules:

```php
#[Validation('required|email|unique:users')]
public string $email;

#[Validation('required|min:8')]
public string $password;
```

### Form Field Attributes

Define form elements:

```php
#[FormField(type: 'email', label: 'Email Address')]
public string $email;

#[FormField(type: 'textarea', rows: 5)]
public string $description;
```

## Complete Example

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('products')]
#[Timestamps]
#[SoftDeletes]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Validation('required|max:255')]
    #[FormField(type: 'text', label: 'Product Name')]
    #[Index]
    public string $name;
    
    #[Field(type: 'string', length: 100)]
    #[Validation('required|unique:products,sku')]
    #[FormField(type: 'text', label: 'SKU')]
    #[Unique]
    public string $sku;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validation('required|numeric|min:0')]
    #[FormField(type: 'number', label: 'Price', step: '0.01')]
    public float $price;
    
    #[Field(type: 'text', nullable: true)]
    #[Validation('nullable|max:1000')]
    #[FormField(type: 'textarea', label: 'Description', rows: 5)]
    public ?string $description;
    
    #[Field(type: 'boolean', default: true)]
    #[FormField(type: 'checkbox', label: 'In Stock')]
    public bool $in_stock;
    
    #[BelongsTo(Category::class)]
    public Category $category;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $category_id;
    
    #[BelongsToMany(Tag::class, pivot: 'product_tags')]
    public array $tags;
}
```

## Auto-Generation from Metadata

### Generate Migration

```bash
php neo make:migration --from-model=Product
```

Creates migration with all columns, indexes, and foreign keys automatically.

### Generate Form

```php
use NeoPhp\Form\FormGenerator;

$form = FormGenerator::fromModel(Product::class);
echo $form->render();
```

Creates complete HTML form with validation.

### Validate Data

```php
use NeoPhp\Validation\Validator;

$validator = Validator::fromModel(Product::class);

if ($validator->validate($request->all())) {
    // Valid
} else {
    $errors = $validator->errors();
}
```

Validates using rules from metadata.

## Metadata Reflection

Access metadata programmatically:

```php
use NeoPhp\Metadata\MetadataReader;

$metadata = MetadataReader::forModel(Product::class);

// Get table name
$tableName = $metadata->getTableName(); // 'products'

// Get all fields
$fields = $metadata->getFields();

// Get specific field
$field = $metadata->getField('price');
echo $field->type;        // 'decimal'
echo $field->precision;   // 10
echo $field->scale;       // 2

// Get relationships
$relationships = $metadata->getRelationships();

// Get validation rules
$rules = $metadata->getValidationRules();
```

## Metadata Inheritance

Child models inherit parent metadata:

```php
#[Table('entities')]
abstract class Entity extends Model
{
    #[ID]
    public int $id;
    
    #[Timestamps]
    public string $created_at;
    public string $updated_at;
}

#[Table('users')]
class User extends Entity
{
    // Inherits id, created_at, updated_at
    
    #[Field(type: 'string')]
    public string $name;
}
```

## Metadata Caching

Metadata is cached for performance:

```bash
# Cache metadata
php neo metadata:cache

# Clear metadata cache
php neo metadata:clear
```

## IDE Support

Modern IDEs understand PHP 8 attributes:

- **Autocomplete**: Attribute names and parameters
- **Type Checking**: Validates attribute usage
- **Navigation**: Jump to attribute definition
- **Refactoring**: Rename attributes safely

## Convention Over Configuration

Metadata uses sensible defaults:

```php
// Minimal definition
#[Field(type: 'string')]
public string $name;

// Expands to:
// - VARCHAR(255)
// - NOT NULL (because not nullable type)
// - No default value
// - No index
```

Override when needed:

```php
#[Field(type: 'string', length: 100, nullable: true, default: 'guest')]
public ?string $name;
```

## Metadata Validation

NeoPhp validates metadata at runtime:

```php
// This will throw exception
#[Field(type: 'string', length: -1)]  // ✗ Invalid length
public string $name;

#[Field(type: 'invalid_type')]         // ✗ Unknown type
public $data;

#[BelongsTo(NonExistentClass::class)]  // ✗ Class not found
public $relation;
```

## Best Practices

### 1. Use Type Hints

```php
// Good ✅
#[Field(type: 'string')]
public string $name;

// Bad ❌
public $name;
```

### 2. Match Field Type to PHP Type

```php
// Good ✅
#[Field(type: 'integer')]
public int $age;

#[Field(type: 'boolean')]
public bool $active;

// Bad ❌
#[Field(type: 'string')]
public int $age;  // Type mismatch
```

### 3. Use Nullable Correctly

```php
// Good ✅
#[Field(type: 'string', nullable: true)]
public ?string $phone;

// Bad ❌
#[Field(type: 'string')]
public ?string $phone;  // Mismatch
```

### 4. Add Validation to Input Fields

```php
// Good ✅
#[Field(type: 'string')]
#[Validation('required|email')]
public string $email;

// Bad ❌
#[Field(type: 'string')]
public string $email;  // No validation
```

### 5. Index Frequently Queried Fields

```php
// Good ✅
#[Field(type: 'string')]
#[Index]
public string $email;

#[Field(type: 'enum', options: ['active', 'inactive'])]
#[Index]
public string $status;
```

## Next Steps

- [Table Attributes](table-attributes.md)
- [Field Attributes](field-attributes.md)
- [Relationships](relationships.md)
- [Validation](validation.md)
- [Form Generation](form-generation.md)
