# Field Attributes

Field attributes define individual column properties for your models.

## Basic Field Attribute

```php
use NeoPhp\Metadata\Attributes\Field;

class User extends Model
{
    #[Field(type: 'string', length: 255)]
    public string $name;
}
```

## Field Types

### String Types

```php
// VARCHAR
#[Field(type: 'string', length: 255)]
public string $name;

// CHAR (fixed-length)
#[Field(type: 'char', length: 10)]
public string $country_code;

// TEXT
#[Field(type: 'text')]
public string $description;

// MEDIUMTEXT
#[Field(type: 'mediumtext')]
public string $content;

// LONGTEXT
#[Field(type: 'longtext')]
public string $body;
```

### Integer Types

```php
// TINYINT (-128 to 127, or 0 to 255 unsigned)
#[Field(type: 'tinyInteger')]
public int $age;

// SMALLINT (-32,768 to 32,767, or 0 to 65,535 unsigned)
#[Field(type: 'smallInteger')]
public int $quantity;

// MEDIUMINT
#[Field(type: 'mediumInteger')]
public int $views;

// INT
#[Field(type: 'integer')]
public int $count;

// BIGINT
#[Field(type: 'bigInteger')]
public int $user_id;

// UNSIGNED
#[Field(type: 'integer', unsigned: true)]
public int $positive_number;
```

### Decimal and Float Types

```php
// DECIMAL (exact precision)
#[Field(type: 'decimal', precision: 10, scale: 2)]
public float $price;

// FLOAT
#[Field(type: 'float', precision: 8, scale: 2)]
public float $rating;

// DOUBLE
#[Field(type: 'double', precision: 15, scale: 8)]
public float $latitude;
```

### Boolean Type

```php
// TINYINT(1) - 0 or 1
#[Field(type: 'boolean', default: false)]
public bool $is_active;
```

### Date and Time Types

```php
// DATE
#[Field(type: 'date')]
public string $birth_date;

// TIME
#[Field(type: 'time')]
public string $start_time;

// DATETIME
#[Field(type: 'datetime')]
public string $published_at;

// TIMESTAMP
#[Field(type: 'timestamp')]
public string $last_login;

// TIMESTAMP with timezone (PostgreSQL)
#[Field(type: 'timestampTz')]
public string $created_at;

// YEAR
#[Field(type: 'year')]
public int $year;
```

### JSON Type

```php
// JSON
#[Field(type: 'json')]
public array $settings;

// JSONB (PostgreSQL - binary JSON, faster queries)
#[Field(type: 'jsonb')]
public array $metadata;
```

### Binary Types

```php
// BLOB
#[Field(type: 'binary')]
public string $file_data;

// MEDIUMBLOB
#[Field(type: 'mediumBinary')]
public string $image_data;

// LONGBLOB
#[Field(type: 'longBinary')]
public string $video_data;
```

### Enum Type

```php
#[Field(type: 'enum', allowed: ['pending', 'active', 'suspended', 'cancelled'])]
public string $status;
```

### UUID Type

```php
#[Field(type: 'uuid')]
public string $id;
```

## Field Options

### Complete Options

```php
#[Field(
    type: 'string',
    length: 255,
    nullable: false,
    default: 'Unknown',
    unique: false,
    index: false,
    unsigned: false,
    comment: 'User full name'
)]
public string $name;
```

### Available Options

| Option | Type | Description |
|--------|------|-------------|
| `type` | string | Required. Column type |
| `length` | int | String/char length |
| `precision` | int | Total digits (decimal/float) |
| `scale` | int | Decimal places |
| `nullable` | bool | Allow NULL values |
| `default` | mixed | Default value |
| `unique` | bool | Unique constraint |
| `index` | bool | Create index |
| `unsigned` | bool | Unsigned numbers |
| `comment` | string | Column comment |
| `allowed` | array | Enum values |

## Nullable Fields

```php
// Nullable string
#[Field(type: 'string', length: 255, nullable: true)]
public ?string $middle_name;

// Nullable integer
#[Field(type: 'integer', nullable: true)]
public ?int $age;

// Required field (default)
#[Field(type: 'string', length: 255)]
public string $email;
```

## Default Values

```php
// String default
#[Field(type: 'string', length: 20, default: 'active')]
public string $status;

// Integer default
#[Field(type: 'integer', default: 0)]
public int $points;

// Boolean default
#[Field(type: 'boolean', default: true)]
public bool $is_verified;

// NULL default
#[Field(type: 'string', length: 255, nullable: true, default: null)]
public ?string $bio;

// Current timestamp
#[Field(type: 'timestamp', default: 'CURRENT_TIMESTAMP')]
public string $created_at;
```

## Indexes

### Single Column Index

```php
#[Field(type: 'string', length: 255, index: true)]
public string $email;
```

### Unique Constraint

```php
#[Field(type: 'string', length: 100, unique: true)]
public string $username;
```

### Composite Indexes

Use table-level indexes for multiple columns:

```php
#[Table('users')]
#[TableIndex(['first_name', 'last_name'])]
class User extends Model
{
    #[Field(type: 'string', length: 100)]
    public string $first_name;
    
    #[Field(type: 'string', length: 100)]
    public string $last_name;
}
```

## Auto-Increment ID

```php
use NeoPhp\Metadata\Attributes\ID;

#[ID]
public int $id;

// Equivalent to:
// #[Field(type: 'bigInteger', unsigned: true, autoIncrement: true)]
// #[PrimaryKey]
// public int $id;
```

## Complete Examples

### User Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('users')]
#[Timestamps]
#[SoftDeletes]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100)]
    public string $first_name;
    
    #[Field(type: 'string', length: 100)]
    public string $last_name;
    
    #[Field(type: 'string', length: 100, nullable: true)]
    public ?string $middle_name;
    
    #[Field(type: 'string', length: 255, unique: true, index: true)]
    public string $email;
    
    #[Field(type: 'string', length: 255)]
    public string $password;
    
    #[Field(type: 'string', length: 20, nullable: true)]
    public ?string $phone;
    
    #[Field(type: 'date', nullable: true)]
    public ?string $birth_date;
    
    #[Field(type: 'enum', allowed: ['male', 'female', 'other'], nullable: true)]
    public ?string $gender;
    
    #[Field(type: 'boolean', default: false)]
    public bool $is_verified;
    
    #[Field(type: 'boolean', default: true)]
    public bool $is_active;
    
    #[Field(type: 'timestamp', nullable: true)]
    public ?string $email_verified_at;
    
    #[Field(type: 'timestamp', nullable: true)]
    public ?string $last_login_at;
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
#[SoftDeletes]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255, index: true)]
    public string $name;
    
    #[Field(type: 'string', length: 100, unique: true)]
    public string $sku;
    
    #[Field(type: 'text', nullable: true)]
    public ?string $description;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    public float $price;
    
    #[Field(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    public ?float $sale_price;
    
    #[Field(type: 'decimal', precision: 5, scale: 2, default: 0)]
    public float $discount_percentage;
    
    #[Field(type: 'integer', unsigned: true, default: 0)]
    public int $stock_quantity;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $category_id;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Index]
    public ?int $brand_id;
    
    #[Field(
        type: 'enum',
        allowed: ['draft', 'active', 'out_of_stock', 'discontinued'],
        default: 'draft'
    )]
    #[Index]
    public string $status;
    
    #[Field(type: 'boolean', default: false)]
    public bool $is_featured;
    
    #[Field(type: 'json', nullable: true)]
    public ?array $specifications;
    
    #[Field(type: 'float', precision: 3, scale: 2, default: 0)]
    public float $weight_kg;
    
    #[Field(type: 'integer', unsigned: true, default: 0)]
    public int $view_count;
}
```

### Order Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('orders')]
#[Timestamps]
class Order extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 50, unique: true)]
    public string $order_number;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(
        type: 'enum',
        allowed: ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
        default: 'pending'
    )]
    #[Index]
    public string $status;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    public float $subtotal;
    
    #[Field(type: 'decimal', precision: 10, scale: 2, default: 0)]
    public float $tax;
    
    #[Field(type: 'decimal', precision: 10, scale: 2, default: 0)]
    public float $shipping;
    
    #[Field(type: 'decimal', precision: 10, scale: 2, default: 0)]
    public float $discount;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    public float $total;
    
    #[Field(type: 'string', length: 10)]
    public string $currency;
    
    #[Field(type: 'json')]
    public array $shipping_address;
    
    #[Field(type: 'json')]
    public array $billing_address;
    
    #[Field(type: 'text', nullable: true)]
    public ?string $notes;
    
    #[Field(type: 'datetime', nullable: true)]
    public ?string $shipped_at;
    
    #[Field(type: 'datetime', nullable: true)]
    public ?string $delivered_at;
}
```

### Blog Post Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('posts')]
#[Timestamps]
#[SoftDeletes]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $title;
    
    #[Field(type: 'string', length: 255, unique: true)]
    public string $slug;
    
    #[Field(type: 'text', nullable: true)]
    public ?string $excerpt;
    
    #[Field(type: 'longtext')]
    public string $content;
    
    #[Field(type: 'string', length: 255, nullable: true)]
    public ?string $featured_image;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Index]
    public ?int $category_id;
    
    #[Field(
        type: 'enum',
        allowed: ['draft', 'published', 'archived'],
        default: 'draft'
    )]
    #[Index]
    public string $status;
    
    #[Field(type: 'datetime', nullable: true)]
    #[Index]
    public ?string $published_at;
    
    #[Field(type: 'integer', unsigned: true, default: 0)]
    public int $view_count;
    
    #[Field(type: 'integer', unsigned: true, default: 0)]
    public int $comment_count;
    
    #[Field(type: 'boolean', default: true)]
    public bool $allow_comments;
    
    #[Field(type: 'json', nullable: true)]
    public ?array $seo_metadata;
}
```

### Setting Model

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('settings')]
#[Timestamps]
class Setting extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100, unique: true)]
    public string $key;
    
    #[Field(type: 'text')]
    public string $value;
    
    #[Field(
        type: 'enum',
        allowed: ['string', 'integer', 'boolean', 'json', 'array'],
        default: 'string'
    )]
    public string $type;
    
    #[Field(type: 'string', length: 255, nullable: true)]
    public ?string $description;
    
    #[Field(type: 'boolean', default: false)]
    public bool $is_public;
}
```

## Migration Generation

From metadata:

```bash
php neo make:migration --from-model=Product
```

Generated migration:

```php
$this->schema->create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name')->index();
    $table->string('sku', 100)->unique();
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    $table->decimal('discount_percentage', 5, 2)->default(0);
    $table->unsignedInteger('stock_quantity')->default(0);
    $table->unsignedBigInteger('category_id')->index();
    $table->unsignedBigInteger('brand_id')->nullable()->index();
    $table->enum('status', ['draft', 'active', 'out_of_stock', 'discontinued'])
        ->default('draft')
        ->index();
    $table->boolean('is_featured')->default(false);
    $table->json('specifications')->nullable();
    $table->float('weight_kg', 3, 2)->default(0);
    $table->unsignedInteger('view_count')->default(0);
    $table->timestamps();
    $table->softDeletes();
});
```

## Validation Generation

```php
use NeoPhp\Validation\Validator;

$rules = Validator::fromModel(Product::class);

// Generated rules:
[
    'name' => ['required', 'string', 'max:255'],
    'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
    'description' => ['nullable', 'string'],
    'price' => ['required', 'numeric', 'min:0'],
    'sale_price' => ['nullable', 'numeric', 'min:0'],
    'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
    'stock_quantity' => ['required', 'integer', 'min:0'],
    'category_id' => ['required', 'integer', 'exists:categories,id'],
    'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
    'status' => ['required', 'in:draft,active,out_of_stock,discontinued'],
    'is_featured' => ['required', 'boolean'],
    'specifications' => ['nullable', 'array'],
    'weight_kg' => ['required', 'numeric', 'min:0'],
    'view_count' => ['required', 'integer', 'min:0'],
]
```

## Best Practices

### 1. Match PHP Types

```php
// Good ✅
#[Field(type: 'string', length: 255)]
public string $name;

#[Field(type: 'integer')]
public int $age;

#[Field(type: 'boolean')]
public bool $is_active;

// Bad ❌
#[Field(type: 'string', length: 255)]
public int $name;  // Type mismatch
```

### 2. Use Nullable Correctly

```php
// Good ✅
#[Field(type: 'string', length: 255, nullable: true)]
public ?string $bio;

// Bad ❌
#[Field(type: 'string', length: 255)]
public ?string $bio;  // Missing nullable: true
```

### 3. Add Indexes for Foreign Keys

```php
// Good ✅
#[Field(type: 'integer', unsigned: true)]
#[Index]
public int $user_id;

// Bad ❌
#[Field(type: 'integer', unsigned: true)]
public int $user_id;  // Missing index
```

### 4. Use Appropriate String Lengths

```php
// Good ✅
#[Field(type: 'string', length: 255)]  // Email, URL
public string $email;

#[Field(type: 'string', length: 100)]  // Username
public string $username;

#[Field(type: 'string', length: 2)]  // Country code
public string $country;

// Bad ❌
#[Field(type: 'string', length: 255)]  // Wasteful
public string $country;
```

### 5. Use Enum for Fixed Values

```php
// Good ✅
#[Field(type: 'enum', allowed: ['pending', 'active', 'cancelled'])]
public string $status;

// Bad ❌
#[Field(type: 'string', length: 20)]
public string $status;  // No constraint
```

### 6. Choose Correct Number Types

```php
// Good ✅
#[Field(type: 'tinyInteger', unsigned: true)]  // 0-255
public int $age;

#[Field(type: 'decimal', precision: 10, scale: 2)]  // Money
public float $price;

// Bad ❌
#[Field(type: 'float')]  // Imprecise for money
public float $price;
```

### 7. Add Comments for Complex Fields

```php
// Good ✅
#[Field(
    type: 'json',
    comment: 'Product specifications: dimensions, materials, certifications'
)]
public array $specifications;
```

## Next Steps

- [Relationships](relationships.md)
- [Validation](validation.md)
- [Form Generation](form-generation.md)
- [Table Attributes](table-attributes.md)
