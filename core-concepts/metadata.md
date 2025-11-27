# Metadata System

NeoPhp's metadata system is one of its most powerful features. It allows you to define database schemas, validation rules, relationships, and form generation all in one place using PHP attributes.

## What is Metadata?

Metadata is "data about data" - in NeoPhp, it's annotations on your models that describe:
- Database table structure
- Field types and constraints
- Relationships between models
- Validation rules
- Form generation settings
- Display preferences

## Why Use Metadata?

### Traditional Approach (Multiple Files)

```php
// Model.php
class User extends Model {}

// migrations/create_users_table.php
Schema::create('users', function(Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    // ...
});

// Validation rules somewhere
$rules = [
    'email' => 'required|email|unique:users',
    // ...
];

// Form generation elsewhere
// More code...
```

### NeoPhp Approach (Single File)

```php
#[Table('users')]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255, unique: true)]
    #[Validation('required|email|unique:users')]
    #[FormField(type: 'email', label: 'Email Address')]
    public string $email;
}
```

Everything in one place! Migrations, validation, and forms are automatically generated.

## Table Attributes

### Basic Table Definition

```php
use NeoPhp\Metadata\Attributes\Table;

#[Table('users')]
class User extends Model
{
    // Model code
}
```

### With Options

```php
#[Table(
    name: 'users',
    engine: 'InnoDB',
    charset: 'utf8mb4',
    collation: 'utf8mb4_unicode_ci',
    comment: 'User accounts table'
)]
class User extends Model
{
    // Model code
}
```

### Timestamps

```php
#[Table('users')]
#[Timestamps] // Adds created_at and updated_at
class User extends Model
{
    // Automatically has timestamps
}

// Or customize
#[Table('users')]
#[Timestamps(
    createdAt: 'created_date',
    updatedAt: 'modified_date'
)]
class User extends Model {}
```

### Soft Deletes

```php
#[Table('users')]
#[SoftDeletes] // Adds deleted_at
class User extends Model
{
    // Soft delete enabled
}

// Or customize
#[Table('users')]
#[SoftDeletes(column: 'archived_at')]
class User extends Model {}
```

## Field Attributes

### ID Field

```php
#[ID]
public int $id;

// Or with options
#[ID(autoIncrement: true, unsigned: true)]
public int $id;

// UUID instead of integer
#[ID(type: 'uuid')]
public string $id;
```

### String Fields

```php
#[Field(type: 'string', length: 255)]
public string $name;

#[Field(type: 'string', length: 100, nullable: true)]
public ?string $middle_name;

#[Field(type: 'string', length: 50, default: 'active')]
public string $status;

#[Field(type: 'text')]
public string $bio;

#[Field(type: 'longtext')]
public string $content;
```

### Numeric Fields

```php
#[Field(type: 'integer')]
public int $age;

#[Field(type: 'integer', unsigned: true)]
public int $quantity;

#[Field(type: 'decimal', precision: 10, scale: 2)]
public float $price;

#[Field(type: 'float')]
public float $rating;

#[Field(type: 'bigint', unsigned: true)]
public int $views;
```

### Date and Time Fields

```php
#[Field(type: 'date')]
public string $birth_date;

#[Field(type: 'time')]
public string $meeting_time;

#[Field(type: 'datetime')]
public string $published_at;

#[Field(type: 'timestamp', default: 'CURRENT_TIMESTAMP')]
public string $last_login;
```

### Boolean Fields

```php
#[Field(type: 'boolean', default: false)]
public bool $is_active;

#[Field(type: 'boolean', default: true)]
public bool $email_verified;
```

### JSON Fields

```php
#[Field(type: 'json')]
public array $settings;

#[Field(type: 'json', nullable: true)]
public ?array $metadata;
```

### Enum Fields

```php
#[Field(type: 'enum', options: ['draft', 'published', 'archived'])]
public string $status;

#[Field(
    type: 'enum',
    options: ['admin', 'editor', 'user'],
    default: 'user'
)]
public string $role;
```

### Indexes

```php
#[Field(type: 'string', length: 255)]
#[Index] // Regular index
public string $email;

#[Field(type: 'string', length: 255)]
#[Unique] // Unique index
public string $username;

#[Field(type: 'string', length: 100)]
#[FullText] // Full-text index
public string $description;
```

## Relationship Attributes

### One-to-One

```php
#[Table('users')]
class User extends Model
{
    #[HasOne(Profile::class)]
    public ?Profile $profile;
}

#[Table('profiles')]
class Profile extends Model
{
    #[BelongsTo(User::class)]
    public User $user;
    
    #[Field(type: 'integer', unsigned: true)]
    public int $user_id;
}
```

### One-to-Many

```php
#[Table('users')]
class User extends Model
{
    #[HasMany(Post::class)]
    public array $posts;
}

#[Table('posts')]
class Post extends Model
{
    #[BelongsTo(User::class)]
    public User $author;
    
    #[Field(type: 'integer', unsigned: true)]
    public int $user_id;
}
```

### Many-to-Many

```php
#[Table('users')]
class User extends Model
{
    #[BelongsToMany(
        related: Role::class,
        pivot: 'user_roles',
        foreignKey: 'user_id',
        relatedKey: 'role_id'
    )]
    public array $roles;
}

#[Table('roles')]
class Role extends Model
{
    #[BelongsToMany(
        related: User::class,
        pivot: 'user_roles',
        foreignKey: 'role_id',
        relatedKey: 'user_id'
    )]
    public array $users;
}
```

### Polymorphic Relationships

```php
#[Table('comments')]
class Comment extends Model
{
    #[MorphTo]
    public Model $commentable;
    
    #[Field(type: 'integer', unsigned: true)]
    public int $commentable_id;
    
    #[Field(type: 'string', length: 255)]
    public string $commentable_type;
}

#[Table('posts')]
class Post extends Model
{
    #[MorphMany(Comment::class, name: 'commentable')]
    public array $comments;
}

#[Table('videos')]
class Video extends Model
{
    #[MorphMany(Comment::class, name: 'commentable')]
    public array $comments;
}
```

## Validation Attributes

### Basic Validation

```php
#[Field(type: 'string', length: 255)]
#[Validation('required|email|unique:users')]
public string $email;

#[Field(type: 'string', length: 255)]
#[Validation('required|min:8')]
public string $password;

#[Field(type: 'integer')]
#[Validation('required|integer|min:18|max:100')]
public int $age;
```

### Multiple Rules

```php
#[Field(type: 'string', length: 255)]
#[Validation('required')]
#[Validation('email')]
#[Validation('unique:users,email')]
public string $email;
```

### Custom Messages

```php
#[Field(type: 'string', length: 255)]
#[Validation(
    rules: 'required|email',
    messages: [
        'required' => 'Please provide your email address',
        'email' => 'Please provide a valid email address'
    ]
)]
public string $email;
```

### Conditional Validation

```php
#[Field(type: 'string', length: 255, nullable: true)]
#[Validation('required_if:type,business')]
public ?string $company_name;

#[Field(type: 'string', length: 255, nullable: true)]
#[Validation('required_with:street_address')]
public ?string $city;
```

## Form Field Attributes

### Basic Form Fields

```php
#[Field(type: 'string', length: 255)]
#[FormField(type: 'text', label: 'Full Name')]
public string $name;

#[Field(type: 'string', length: 255)]
#[FormField(type: 'email', label: 'Email Address')]
public string $email;

#[Field(type: 'string', length: 255)]
#[FormField(type: 'password', label: 'Password')]
public string $password;

#[Field(type: 'text')]
#[FormField(type: 'textarea', label: 'Biography', rows: 5)]
public string $bio;
```

### Select Fields

```php
#[Field(type: 'enum', options: ['admin', 'editor', 'user'])]
#[FormField(
    type: 'select',
    label: 'Role',
    options: [
        'admin' => 'Administrator',
        'editor' => 'Editor',
        'user' => 'Regular User'
    ]
)]
public string $role;
```

### Checkbox and Radio

```php
#[Field(type: 'boolean', default: false)]
#[FormField(type: 'checkbox', label: 'Subscribe to newsletter')]
public bool $newsletter;

#[Field(type: 'string', length: 10)]
#[FormField(
    type: 'radio',
    label: 'Gender',
    options: ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']
)]
public string $gender;
```

### Date and Time Pickers

```php
#[Field(type: 'date')]
#[FormField(type: 'date', label: 'Birth Date')]
public string $birth_date;

#[Field(type: 'datetime')]
#[FormField(type: 'datetime', label: 'Event Date & Time')]
public string $event_at;

#[Field(type: 'time')]
#[FormField(type: 'time', label: 'Meeting Time')]
public string $meeting_time;
```

### File Uploads

```php
#[Field(type: 'string', length: 255)]
#[FormField(
    type: 'file',
    label: 'Profile Picture',
    accept: 'image/*',
    maxSize: '2M'
)]
public string $avatar;

#[Field(type: 'json')]
#[FormField(
    type: 'file',
    label: 'Documents',
    multiple: true,
    accept: '.pdf,.doc,.docx'
)]
public array $documents;
```

### Hidden and Readonly

```php
#[Field(type: 'string', length: 255)]
#[FormField(type: 'hidden')]
public string $csrf_token;

#[Field(type: 'string', length: 255)]
#[FormField(type: 'text', label: 'Username', readonly: true)]
public string $username;
```

## Complete Example: Blog Post

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
    
    // Title
    #[Field(type: 'string', length: 255)]
    #[Validation('required|min:5|max:255')]
    #[FormField(type: 'text', label: 'Post Title', placeholder: 'Enter post title')]
    #[Index]
    public string $title;
    
    // Slug
    #[Field(type: 'string', length: 255)]
    #[Validation('required|unique:posts,slug')]
    #[FormField(type: 'text', label: 'URL Slug')]
    #[Unique]
    public string $slug;
    
    // Content
    #[Field(type: 'longtext')]
    #[Validation('required|min:100')]
    #[FormField(type: 'textarea', label: 'Content', rows: 15, class: 'wysiwyg')]
    #[FullText]
    public string $content;
    
    // Excerpt
    #[Field(type: 'text', nullable: true)]
    #[Validation('max:500')]
    #[FormField(type: 'textarea', label: 'Excerpt (Optional)', rows: 3)]
    public ?string $excerpt;
    
    // Status
    #[Field(type: 'enum', options: ['draft', 'published', 'archived'], default: 'draft')]
    #[Validation('required|in:draft,published,archived')]
    #[FormField(
        type: 'select',
        label: 'Status',
        options: [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived'
        ]
    )]
    #[Index]
    public string $status;
    
    // Featured Image
    #[Field(type: 'string', length: 255, nullable: true)]
    #[FormField(
        type: 'file',
        label: 'Featured Image',
        accept: 'image/*',
        maxSize: '5M'
    )]
    public ?string $featured_image;
    
    // Published Date
    #[Field(type: 'datetime', nullable: true)]
    #[FormField(type: 'datetime', label: 'Publish Date')]
    public ?string $published_at;
    
    // View Count
    #[Field(type: 'integer', unsigned: true, default: 0)]
    public int $views = 0;
    
    // Meta Tags (JSON)
    #[Field(type: 'json', nullable: true)]
    #[FormField(type: 'textarea', label: 'Meta Tags (JSON)', rows: 3)]
    public ?array $meta_tags;
    
    // Relationships
    #[BelongsTo(User::class)]
    public User $author;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[BelongsTo(Category::class)]
    public Category $category;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $category_id;
    
    #[BelongsToMany(
        related: Tag::class,
        pivot: 'post_tags',
        foreignKey: 'post_id',
        relatedKey: 'tag_id'
    )]
    public array $tags;
    
    #[HasMany(Comment::class)]
    public array $comments;
    
    #[MorphMany(Image::class, name: 'imageable')]
    public array $images;
}
```

## Generating from Metadata

### Generate Migration

```bash
php neo make:migration --from-model=Post
```

This creates a migration file from your metadata!

### Generate Form

```php
use NeoPhp\Form\FormGenerator;

$form = FormGenerator::fromModel(Post::class);
echo $form->render();
```

### Validate Data

```php
$validator = Validator::fromModel(Post::class);

if ($validator->validate($request->all())) {
    // Valid
} else {
    $errors = $validator->errors();
}
```

## Best Practices

### 1. Keep Metadata Close to Model

```php
// Good ✅ - Everything in one place
#[Table('users')]
class User extends Model
{
    #[Field(type: 'string', length: 255)]
    #[Validation('required|email')]
    #[FormField(type: 'email', label: 'Email')]
    public string $email;
}
```

### 2. Use Type Hints

```php
// Good ✅
#[Field(type: 'string', length: 255)]
public string $name;

#[Field(type: 'integer', unsigned: true)]
public int $age;

// Bad ❌
public $name;
public $age;
```

### 3. Make Nullable Fields Explicit

```php
// Good ✅
#[Field(type: 'string', length: 255, nullable: true)]
public ?string $middle_name;

// Bad ❌
#[Field(type: 'string', length: 255)]
public $middle_name; // Is it nullable?
```

### 4. Add Validation to All Input Fields

```php
// Good ✅
#[Field(type: 'string', length: 255)]
#[Validation('required|email|unique:users')]
public string $email;

// Bad ❌
#[Field(type: 'string', length: 255)]
public string $email; // No validation!
```

### 5. Use Indexes Appropriately

```php
// Good ✅ - Index frequently queried fields
#[Field(type: 'string', length: 255)]
#[Unique]
public string $email;

#[Field(type: 'string', length: 50)]
#[Index]
public string $status;

// Don't index everything
```

## Next Steps

- [Table Attributes Reference](../metadata/table-attributes.md)
- [Field Attributes Reference](../metadata/field-attributes.md)
- [Relationship Attributes Reference](../metadata/relationships.md)
- [Form Generation](../metadata/form-generation.md)
