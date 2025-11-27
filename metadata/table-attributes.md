# Table Attributes

Table attributes define table-level settings for your models.

## Basic Table Attribute

```php
use NeoPhp\Metadata\Attributes\Table;

#[Table('users')]
class User extends Model
{
    // Model properties
}
```

## Table Options

### Complete Example

```php
#[Table(
    name: 'users',
    engine: 'InnoDB',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci',
    comment: 'User accounts table'
)]
class User extends Model {}
```

### Available Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `name` | string | Required | Table name |
| `engine` | string | 'InnoDB' | Storage engine (MySQL) |
| `charset` | string | 'utf8mb4' | Character set |
| `collation` | string | 'utf8mb4_unicode_ci' | Collation |
| `comment` | string | null | Table comment |
| `temporary` | bool | false | Temporary table |

## Timestamps

Automatically add `created_at` and `updated_at` columns:

```php
use NeoPhp\Metadata\Attributes\Timestamps;

#[Table('users')]
#[Timestamps]
class User extends Model {}
```

Generated columns:
```sql
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

### Custom Column Names

```php
#[Timestamps(
    createdAt: 'created_date',
    updatedAt: 'modified_date'
)]
class User extends Model {}
```

### With Timezone

```php
#[TimestampsTz]  // TIMESTAMPTZ (PostgreSQL)
class User extends Model {}
```

## Soft Deletes

Add `deleted_at` column for soft deleting records:

```php
use NeoPhp\Metadata\Attributes\SoftDeletes;

#[Table('users')]
#[SoftDeletes]
class User extends Model {}
```

Generated column:
```sql
deleted_at TIMESTAMP NULL
```

### Custom Column Name

```php
#[SoftDeletes(column: 'archived_at')]
class User extends Model {}
```

### With Timezone

```php
#[SoftDeletesTz]  // TIMESTAMPTZ
class User extends Model {}
```

## Primary Key

### Auto-Increment ID

```php
use NeoPhp\Metadata\Attributes\ID;

#[Table('users')]
class User extends Model
{
    #[ID]
    public int $id;
}
```

Generated:
```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
```

### UUID Primary Key

```php
#[Table('users')]
class User extends Model
{
    #[ID(type: 'uuid')]
    public string $id;
}
```

Generated:
```sql
id CHAR(36) PRIMARY KEY
```

### ULID Primary Key

```php
#[Table('users')]
class User extends Model
{
    #[ID(type: 'ulid')]
    public string $id;
}
```

### Custom Primary Key

```php
#[Table('users')]
class User extends Model
{
    #[ID(autoIncrement: false)]
    public string $username;
}
```

### Composite Primary Key

```php
#[Table('user_roles')]
#[PrimaryKey(['user_id', 'role_id'])]
class UserRole extends Model
{
    public int $user_id;
    public int $role_id;
}
```

## Table Indexes

### Table-Level Indexes

```php
use NeoPhp\Metadata\Attributes\{Table, TableIndex};

#[Table('products')]
#[TableIndex(['category_id', 'status'])]
#[TableIndex(['name', 'sku'], name: 'products_name_sku_index')]
class Product extends Model {}
```

### Unique Constraints

```php
#[Table('users')]
#[UniqueConstraint(['email', 'tenant_id'])]
class User extends Model {}
```

### Full-Text Index

```php
#[Table('posts')]
#[FullTextIndex(['title', 'content'])]
class Post extends Model {}
```

## Storage Engine (MySQL)

```php
#[Table('logs', engine: 'MyISAM')]
class Log extends Model {}

#[Table('sessions', engine: 'MEMORY')]
class Session extends Model {}
```

## Character Set and Collation

```php
#[Table(
    name: 'posts',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci'
)]
class Post extends Model {}

// Case-insensitive collation
#[Table(
    name: 'users',
    collation: 'utf8mb4_general_ci'
)]
class User extends Model {}
```

## Table Comments

```php
#[Table(
    name: 'orders',
    comment: 'Customer orders table. Contains all order records with status tracking.'
)]
class Order extends Model {}
```

## Temporary Tables

```php
#[Table('temp_calculations', temporary: true)]
class TempCalculation extends Model {}
```

## Complete Examples

### E-commerce Product Table

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table(
    name: 'products',
    engine: 'InnoDB',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci',
    comment: 'Product catalog with inventory tracking'
)]
#[Timestamps]
#[SoftDeletes]
#[TableIndex(['category_id', 'status'])]
#[TableIndex(['brand_id', 'status'])]
#[FullTextIndex(['name', 'description'])]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Index]
    public string $name;
    
    #[Field(type: 'string', length: 100)]
    #[Unique]
    public string $sku;
    
    // Other fields...
}
```

### Blog Post Table

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('posts')]
#[Timestamps]
#[SoftDeletes]
#[TableIndex(['user_id', 'status', 'published_at'])]
#[FullTextIndex(['title', 'content', 'excerpt'])]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $title;
    
    #[Field(type: 'string', length: 255)]
    #[Unique]
    public string $slug;
    
    // Other fields...
}
```

### Pivot Table

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('post_tags')]
#[PrimaryKey(['post_id', 'tag_id'])]
#[Timestamps]
class PostTag extends Model
{
    #[Field(type: 'integer', unsigned: true)]
    public int $post_id;
    
    #[Field(type: 'integer', unsigned: true)]
    public int $tag_id;
    
    #[Field(type: 'integer', default: 0)]
    public int $order;
}
```

### Audit Log Table

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table(
    name: 'audit_logs',
    engine: 'InnoDB',
    comment: 'System audit trail for all user actions'
)]
#[Timestamps(createdAt: 'logged_at', updatedAt: null)]
#[TableIndex(['user_id', 'logged_at'])]
#[TableIndex(['action', 'logged_at'])]
class AuditLog extends Model
{
    #[ID(type: 'uuid')]
    public string $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(type: 'string', length: 50)]
    #[Index]
    public string $action;
    
    #[Field(type: 'json')]
    public array $changes;
    
    #[Field(type: 'string', length: 45)]
    public string $ip_address;
}
```

## Migration Generation

When you generate a migration from metadata:

```bash
php neo make:migration --from-model=Product
```

It creates:

```php
<?php

use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('sku', 100)->unique();
            // ... all fields from metadata
            $table->timestamps();
            $table->softDeletes();
            
            // Table-level indexes
            $table->index(['category_id', 'status']);
            $table->fulltext(['name', 'description']);
        });
        
        // Table options
        $table->engine = 'InnoDB';
        $table->charset = 'utf8mb4';
        $table->collation = 'utf8mb4_unicode_ci';
    }

    public function down(): void
    {
        $this->schema->dropIfExists('products');
    }
};
```

## Best Practices

### 1. Always Specify Table Name

```php
// Good ✅
#[Table('users')]
class User extends Model {}

// Bad ❌ - Relies on convention
class User extends Model {}
```

### 2. Use Timestamps for Auditing

```php
// Good ✅
#[Table('orders')]
#[Timestamps]
class Order extends Model {}
```

### 3. Add Soft Deletes for Important Data

```php
// Good ✅
#[Table('users')]
#[SoftDeletes]
class User extends Model {}

// Don't hard delete users
```

### 4. Index Frequently Queried Columns

```php
// Good ✅
#[Table('orders')]
#[TableIndex(['user_id', 'status'])]
#[TableIndex(['created_at'])]
class Order extends Model {}
```

### 5. Add Comments for Complex Tables

```php
// Good ✅
#[Table(
    name: 'subscriptions',
    comment: 'User subscriptions with billing cycles and auto-renewal settings'
)]
class Subscription extends Model {}
```

### 6. Use Appropriate Storage Engine

```php
// Transactional data - InnoDB
#[Table('orders', engine: 'InnoDB')]
class Order extends Model {}

// Full-text search - MyISAM
#[Table('search_index', engine: 'MyISAM')]
class SearchIndex extends Model {}

// Session data - MEMORY
#[Table('active_sessions', engine: 'MEMORY')]
class ActiveSession extends Model {}
```

## Next Steps

- [Field Attributes](field-attributes.md)
- [Relationships](relationships.md)
- [Validation](validation.md)
- [Form Generation](form-generation.md)
