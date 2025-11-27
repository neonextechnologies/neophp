# Model Generator

The model generator creates model classes with metadata attributes, making it easy to define your application's data structure.

## Basic Usage

```bash
# Generate a simple model
php neo make:model User

# Generate with migration
php neo make:model Product --migration

# Generate with everything
php neo make:model Post --all
```

## Command Syntax

```bash
php neo make:model [name] [options]
```

### Arguments

- `name` - The name of the model (e.g., User, Product, BlogPost)

### Options

- `-m, --migration` - Create a migration file
- `-c, --controller` - Create a controller
- `-f, --form` - Create a form
- `-a, --all` - Create model, migration, controller, and form
- `--force` - Overwrite existing files
- `--fields` - Define fields inline

## Examples

### Simple Model

```bash
php neo make:model Product
```

Creates `app/Models/Product.php`:

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
    public string $name;
}
```

### Model with Migration

```bash
php neo make:model Product --migration
```

Creates:
1. `app/Models/Product.php` (model)
2. `database/migrations/xxx_create_products_table.php` (migration)

### Model with Controller

```bash
php neo make:model Product --controller
```

Creates:
1. `app/Models/Product.php` (model)
2. `app/Controllers/ProductController.php` (controller with CRUD methods)

### Complete Model (All Files)

```bash
php neo make:model Product --all
```

Creates:
1. `app/Models/Product.php` (model)
2. `app/Controllers/ProductController.php` (controller)
3. `database/migrations/xxx_create_products_table.php` (migration)
4. `views/products/` (view templates)
5. Forms configuration

## Defining Fields Inline

You can define fields when generating the model:

```bash
php neo make:model Product --fields="name:string,price:decimal,description:text,in_stock:boolean"
```

Creates a model with all specified fields:

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
    #[FormField(type: 'text', label: 'Name')]
    public string $name;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    #[Validation('required|numeric|min:0')]
    #[FormField(type: 'number', label: 'Price', step: '0.01')]
    public float $price;
    
    #[Field(type: 'text')]
    #[Validation('nullable')]
    #[FormField(type: 'textarea', label: 'Description', rows: 5)]
    public string $description;
    
    #[Field(type: 'boolean', default: true)]
    #[FormField(type: 'checkbox', label: 'In Stock')]
    public bool $in_stock;
}
```

## Field Type Mapping

| Field Type | Database Type | PHP Type | Form Field |
|------------|---------------|----------|------------|
| `string` | VARCHAR(255) | string | text |
| `text` | TEXT | string | textarea |
| `integer` | INT | int | number |
| `bigint` | BIGINT | int | number |
| `decimal` | DECIMAL | float | number |
| `float` | FLOAT | float | number |
| `boolean` | TINYINT(1) | bool | checkbox |
| `date` | DATE | string | date |
| `datetime` | DATETIME | string | datetime |
| `time` | TIME | string | time |
| `json` | JSON | array | textarea |
| `enum` | ENUM | string | select |

## Field Modifiers

Add modifiers to fields:

```bash
# Nullable field
php neo make:model User --fields="middle_name:string:nullable"

# With default value
php neo make:model Product --fields="status:string:default:active"

# Unique field
php neo make:model User --fields="email:string:unique"

# Unsigned integer
php neo make:model Product --fields="quantity:integer:unsigned"

# Multiple modifiers
php neo make:model User --fields="email:string:unique:nullable:default:null"
```

## Relationship Shortcuts

```bash
# Belongs to User
php neo make:model Post --fields="user_id:belongsTo:User,title:string,content:text"

# Has many Comments
php neo make:model Post --fields="title:string" --relationships="comments:hasMany:Comment"

# Many to many Tags
php neo make:model Post --fields="title:string" --relationships="tags:belongsToMany:Tag"
```

This generates proper relationship attributes:

```php
#[BelongsTo(User::class)]
public User $user;

#[Field(type: 'integer', unsigned: true)]
public int $user_id;

#[HasMany(Comment::class)]
public array $comments;

#[BelongsToMany(Tag::class)]
public array $tags;
```

## Advanced Examples

### E-commerce Product

```bash
php neo make:model Product --all --fields="
name:string,
sku:string:unique,
price:decimal,
cost:decimal:nullable,
description:text:nullable,
category_id:belongsTo:Category,
brand_id:belongsTo:Brand,
in_stock:boolean:default:true,
stock_quantity:integer:unsigned:default:0,
weight:decimal:nullable,
dimensions:json:nullable,
featured_image:string:nullable,
status:enum:draft,published,archived:default:draft
"
```

### Blog Post

```bash
php neo make:model Post --all --fields="
title:string,
slug:string:unique,
excerpt:text:nullable,
content:text,
user_id:belongsTo:User,
category_id:belongsTo:Category,
featured_image:string:nullable,
published_at:datetime:nullable,
views:integer:unsigned:default:0,
status:enum:draft,published,archived:default:draft
" --relationships="tags:belongsToMany:Tag,comments:hasMany:Comment"
```

### User Model

```bash
php neo make:model User --migration --fields="
name:string,
email:string:unique,
password:string,
email_verified_at:datetime:nullable,
remember_token:string:nullable,
role:enum:admin,editor,user:default:user,
is_active:boolean:default:true,
last_login_at:datetime:nullable,
avatar:string:nullable,
bio:text:nullable
"
```

## Customizing Generated Models

### With Soft Deletes

```bash
php neo make:model Post --soft-deletes
```

Adds `#[SoftDeletes]` attribute:

```php
#[Table('posts')]
#[Timestamps]
#[SoftDeletes]
class Post extends Model
{
    // Model code
}
```

### Without Timestamps

```bash
php neo make:model Log --no-timestamps
```

Generates model without `#[Timestamps]` attribute.

### Custom Table Name

```bash
php neo make:model Product --table=inventory_products
```

```php
#[Table('inventory_products')]
class Product extends Model
{
    // Model code
}
```

## Interactive Mode

Run without arguments for interactive prompts:

```bash
php neo make:model

# Prompts:
# Model name: Product
# Generate migration? (yes/no): yes
# Generate controller? (yes/no): yes
# Add fields? (yes/no): yes
# Field name (empty to finish): name
# Field type: string
# Field modifiers (nullable, unique, etc.): 
# Field name (empty to finish): price
# Field type: decimal
# Field modifiers: 
# Field name (empty to finish): 
# Creating model...
```

## Model Templates

### Custom Template

Create custom template at `stubs/model.stub`:

```php
<?php

namespace {{namespace}};

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

/**
 * {{class}} Model
 * 
 * @author Your Name
 * @created {{date}}
 */
#[Table('{{table}}')]
#[Timestamps]
class {{class}} extends Model
{
    {{fields}}
}
```

Use custom template:

```bash
php neo make:model Product --template=stubs/model.stub
```

## Model Namespaces

Generate models in subdirectories:

```bash
# Creates app/Models/Blog/Post.php
php neo make:model Blog/Post

# Creates app/Models/Shop/Product.php
php neo make:model Shop/Product

# Creates app/Models/Admin/User.php
php neo make:model Admin/User
```

Generated namespace:

```php
namespace App\Models\Blog;

class Post extends Model
{
    // Model code
}
```

## Updating Existing Models

```bash
# Add fields to existing model
php neo model:add-field Product --fields="featured:boolean:default:false"

# Remove field from model
php neo model:remove-field Product --field=featured

# Add relationship
php neo model:add-relationship Post --relationship="tags:belongsToMany:Tag"
```

## Best Practices

### 1. Use Descriptive Names

```bash
# Good ✅
php neo make:model BlogPost
php neo make:model ProductCategory
php neo make:model UserProfile

# Bad ❌
php neo make:model Post1
php neo make:model Data
php neo make:model Temp
```

### 2. Generate Related Files Together

```bash
# Good ✅ - Everything in one command
php neo make:model Product --all

# Bad ❌ - Multiple commands
php neo make:model Product
php neo make:controller ProductController
php neo make:migration create_products_table
```

### 3. Use Inline Fields for Simple Models

```bash
# Good ✅ - Quick and clear
php neo make:model Tag --fields="name:string:unique,slug:string:unique"

# For complex models, edit manually after generation
```

### 4. Follow Naming Conventions

- Singular model names (User, not Users)
- PascalCase for model names (BlogPost, not blog_post)
- Table names are automatically pluralized (User → users)

### 5. Add Validation and Forms

```bash
# Good ✅ - Generate with form for user input
php neo make:model Contact --migration --form

# Forms include validation automatically
```

## Troubleshooting

### Model Already Exists

```bash
# Use --force to overwrite
php neo make:model Product --force

# Or rename the existing file first
mv app/Models/Product.php app/Models/Product.backup.php
```

### Namespace Issues

```bash
# Ensure proper namespace in composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}

# Run composer dump-autoload
composer dump-autoload
```

### Field Type Not Recognized

Check supported field types:

```bash
php neo make:model --help-fields
```

## Next Steps

- [Controller Generator](controller.md)
- [Migration Generator](migration.md)
- [Form Generator](form.md)
- [CRUD Generator](crud.md)
- [Model Metadata Reference](../metadata/field-attributes.md)
