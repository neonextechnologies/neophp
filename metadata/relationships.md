# Relationships

Define relationships between models using attributes.

## Relationship Types

NeoPhp supports all standard relationship types:

- One-to-One
- One-to-Many
- Many-to-One (Inverse)
- Many-to-Many
- Has-Many-Through
- Polymorphic Relations

## One-to-One

A user has one profile:

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
    
    #[Field(type: 'string', length: 255)]
    public string $name;
    
    #[HasOne(Profile::class, foreignKey: 'user_id')]
    public ?Profile $profile;
}

#[Table('profiles')]
class Profile extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(type: 'text', nullable: true)]
    public ?string $bio;
    
    #[BelongsTo(User::class, foreignKey: 'user_id')]
    public User $user;
}
```

Usage:

```php
$user = User::find(1);
echo $user->profile->bio;

$profile = Profile::find(1);
echo $profile->user->name;
```

## One-to-Many

A user has many posts:

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
    
    #[HasMany(Post::class, foreignKey: 'user_id')]
    public array $posts;
}

#[Table('posts')]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(type: 'string', length: 255)]
    public string $title;
    
    #[BelongsTo(User::class, foreignKey: 'user_id')]
    public User $user;
}
```

Usage:

```php
$user = User::find(1);
foreach ($user->posts as $post) {
    echo $post->title;
}

$post = Post::find(1);
echo $post->user->name;
```

## Many-to-Many

Posts have many tags:

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
    public string $title;
    
    #[BelongsToMany(
        Tag::class,
        through: 'post_tags',
        foreignKey: 'post_id',
        relatedKey: 'tag_id'
    )]
    public array $tags;
}

#[Table('tags')]
class Tag extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100, unique: true)]
    public string $name;
    
    #[BelongsToMany(
        Post::class,
        through: 'post_tags',
        foreignKey: 'tag_id',
        relatedKey: 'post_id'
    )]
    public array $posts;
}

#[Table('post_tags')]
#[PrimaryKey(['post_id', 'tag_id'])]
class PostTag extends Model
{
    #[Field(type: 'integer', unsigned: true)]
    public int $post_id;
    
    #[Field(type: 'integer', unsigned: true)]
    public int $tag_id;
}
```

Usage:

```php
$post = Post::find(1);
foreach ($post->tags as $tag) {
    echo $tag->name;
}

$tag = Tag::find(1);
foreach ($tag->posts as $post) {
    echo $post->title;
}

// Attach
$post->tags()->attach($tagId);

// Detach
$post->tags()->detach($tagId);

// Sync
$post->tags()->sync([1, 2, 3]);
```

## Has-Many-Through

A country has many posts through users:

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('countries')]
class Country extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100)]
    public string $name;
    
    #[HasMany(User::class, foreignKey: 'country_id')]
    public array $users;
    
    #[HasManyThrough(
        Post::class,
        through: User::class,
        firstKey: 'country_id',
        secondKey: 'user_id'
    )]
    public array $posts;
}

#[Table('users')]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $country_id;
    
    #[HasMany(Post::class, foreignKey: 'user_id')]
    public array $posts;
    
    #[BelongsTo(Country::class, foreignKey: 'country_id')]
    public Country $country;
}

#[Table('posts')]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[BelongsTo(User::class, foreignKey: 'user_id')]
    public User $user;
}
```

Usage:

```php
$country = Country::find(1);
foreach ($country->posts as $post) {
    echo $post->title;
}
```

## Polymorphic One-to-Many

Comments on posts and videos:

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('comments')]
class Comment extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'text')]
    public string $body;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $commentable_id;
    
    #[Field(type: 'string', length: 255)]
    #[Index]
    public string $commentable_type;
    
    #[MorphTo(name: 'commentable')]
    public Model $commentable;
}

#[Table('posts')]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $title;
    
    #[MorphMany(
        Comment::class,
        name: 'commentable'
    )]
    public array $comments;
}

#[Table('videos')]
class Video extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $title;
    
    #[MorphMany(
        Comment::class,
        name: 'commentable'
    )]
    public array $comments;
}
```

Usage:

```php
$post = Post::find(1);
foreach ($post->comments as $comment) {
    echo $comment->body;
}

$video = Video::find(1);
foreach ($video->comments as $comment) {
    echo $comment->body;
}

$comment = Comment::find(1);
$commentable = $comment->commentable;  // Post or Video
```

## Polymorphic Many-to-Many

Tags on posts and videos:

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('tags')]
class Tag extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100)]
    public string $name;
    
    #[MorphedByMany(
        Post::class,
        name: 'taggable',
        table: 'taggables'
    )]
    public array $posts;
    
    #[MorphedByMany(
        Video::class,
        name: 'taggable',
        table: 'taggables'
    )]
    public array $videos;
}

#[Table('posts')]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[MorphToMany(
        Tag::class,
        name: 'taggable',
        table: 'taggables'
    )]
    public array $tags;
}

#[Table('videos')]
class Video extends Model
{
    #[ID]
    public int $id;
    
    #[MorphToMany(
        Tag::class,
        name: 'taggable',
        table: 'taggables'
    )]
    public array $tags;
}

#[Table('taggables')]
class Taggable extends Model
{
    #[Field(type: 'integer', unsigned: true)]
    public int $tag_id;
    
    #[Field(type: 'integer', unsigned: true)]
    public int $taggable_id;
    
    #[Field(type: 'string', length: 255)]
    public string $taggable_type;
}
```

## Relationship Options

### Foreign Key Customization

```php
#[HasMany(
    Post::class,
    foreignKey: 'author_id',  // Instead of user_id
    localKey: 'id'            // Parent key (default: id)
)]
public array $posts;
```

### Custom Relationship Name

```php
#[BelongsTo(
    User::class,
    foreignKey: 'author_id',
    ownerKey: 'id',
    relation: 'author'  // Relationship name
)]
public User $author;
```

### Eager Loading

```php
#[HasMany(Post::class, eager: true)]
public array $posts;
```

Always eager load this relationship:

```php
$users = User::all();
// Posts automatically loaded
```

## Complete E-commerce Example

```php
<?php

namespace App\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('categories')]
class Category extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $name;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Index]
    public ?int $parent_id;
    
    // Self-referencing
    #[BelongsTo(Category::class, foreignKey: 'parent_id')]
    public ?Category $parent;
    
    #[HasMany(Category::class, foreignKey: 'parent_id')]
    public array $children;
    
    #[HasMany(Product::class, foreignKey: 'category_id')]
    public array $products;
}

#[Table('brands')]
class Brand extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $name;
    
    #[HasMany(Product::class, foreignKey: 'brand_id')]
    public array $products;
}

#[Table('products')]
class Product extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $name;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $category_id;
    
    #[Field(type: 'integer', unsigned: true, nullable: true)]
    #[Index]
    public ?int $brand_id;
    
    #[BelongsTo(Category::class, foreignKey: 'category_id')]
    public Category $category;
    
    #[BelongsTo(Brand::class, foreignKey: 'brand_id')]
    public ?Brand $brand;
    
    #[HasMany(ProductImage::class, foreignKey: 'product_id')]
    public array $images;
    
    #[HasMany(Review::class, foreignKey: 'product_id')]
    public array $reviews;
    
    #[BelongsToMany(
        Tag::class,
        through: 'product_tags',
        foreignKey: 'product_id',
        relatedKey: 'tag_id'
    )]
    public array $tags;
    
    #[HasMany(OrderItem::class, foreignKey: 'product_id')]
    public array $orderItems;
}

#[Table('product_images')]
class ProductImage extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $product_id;
    
    #[Field(type: 'string', length: 255)]
    public string $url;
    
    #[Field(type: 'integer', default: 0)]
    public int $order;
    
    #[BelongsTo(Product::class, foreignKey: 'product_id')]
    public Product $product;
}

#[Table('reviews')]
class Review extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $product_id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(type: 'tinyInteger', unsigned: true)]
    public int $rating;
    
    #[Field(type: 'text')]
    public string $comment;
    
    #[BelongsTo(Product::class, foreignKey: 'product_id')]
    public Product $product;
    
    #[BelongsTo(User::class, foreignKey: 'user_id')]
    public User $user;
}

#[Table('orders')]
class Order extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(type: 'string', length: 50, unique: true)]
    public string $order_number;
    
    #[BelongsTo(User::class, foreignKey: 'user_id')]
    public User $user;
    
    #[HasMany(OrderItem::class, foreignKey: 'order_id')]
    public array $items;
    
    #[HasManyThrough(
        Product::class,
        through: OrderItem::class,
        firstKey: 'order_id',
        secondKey: 'product_id'
    )]
    public array $products;
}

#[Table('order_items')]
class OrderItem extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $order_id;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $product_id;
    
    #[Field(type: 'integer', unsigned: true)]
    public int $quantity;
    
    #[Field(type: 'decimal', precision: 10, scale: 2)]
    public float $price;
    
    #[BelongsTo(Order::class, foreignKey: 'order_id')]
    public Order $order;
    
    #[BelongsTo(Product::class, foreignKey: 'product_id')]
    public Product $product;
}

#[Table('users')]
class User extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    public string $name;
    
    #[HasMany(Order::class, foreignKey: 'user_id')]
    public array $orders;
    
    #[HasMany(Review::class, foreignKey: 'user_id')]
    public array $reviews;
    
    #[HasManyThrough(
        OrderItem::class,
        through: Order::class,
        firstKey: 'user_id',
        secondKey: 'order_id'
    )]
    public array $orderItems;
}

#[Table('tags')]
class Tag extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 100, unique: true)]
    public string $name;
    
    #[BelongsToMany(
        Product::class,
        through: 'product_tags',
        foreignKey: 'tag_id',
        relatedKey: 'product_id'
    )]
    public array $products;
}
```

## Usage Examples

### Accessing Relationships

```php
// One-to-One
$user = User::find(1);
echo $user->profile->bio;

// One-to-Many
$user = User::find(1);
foreach ($user->posts as $post) {
    echo $post->title;
}

// Inverse (Many-to-One)
$post = Post::find(1);
echo $post->user->name;

// Many-to-Many
$post = Post::find(1);
foreach ($post->tags as $tag) {
    echo $tag->name;
}

// Has-Many-Through
$country = Country::find(1);
foreach ($country->posts as $post) {
    echo $post->title;
}
```

### Querying Relationships

```php
// Lazy loading
$user = User::find(1);
$posts = $user->posts;

// Eager loading
$users = User::with('posts')->get();

// Multiple relationships
$users = User::with(['posts', 'profile'])->get();

// Nested eager loading
$users = User::with('posts.comments')->get();

// Lazy eager loading
$users = User::all();
$users->load('posts');

// Relationship existence
$users = User::has('posts')->get();

// Relationship counts
$users = User::withCount('posts')->get();
echo $users[0]->posts_count;
```

### Relationship Conditions

```php
// Where has
$users = User::whereHas('posts', function($query) {
    $query->where('status', 'published');
})->get();

// Or where has
$users = User::whereHas('posts', function($query) {
    $query->where('views', '>', 1000);
})->orWhereHas('comments')->get();

// Doesn't have
$users = User::doesntHave('posts')->get();
```

## Migration Generation

From relationships metadata:

```bash
php neo make:migration --from-model=Product
```

Generated:

```php
$this->schema->create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->unsignedBigInteger('category_id');
    $table->unsignedBigInteger('brand_id')->nullable();
    
    $table->foreign('category_id')
        ->references('id')
        ->on('categories')
        ->onDelete('restrict');
        
    $table->foreign('brand_id')
        ->references('id')
        ->on('brands')
        ->onDelete('set null');
});
```

## Best Practices

### 1. Always Index Foreign Keys

```php
// Good ✅
#[Field(type: 'integer', unsigned: true)]
#[Index]
public int $user_id;

// Bad ❌
#[Field(type: 'integer', unsigned: true)]
public int $user_id;
```

### 2. Use Correct Inverse Relationships

```php
// Good ✅
class Post {
    #[BelongsTo(User::class)]
    public User $user;
}

class User {
    #[HasMany(Post::class)]
    public array $posts;
}
```

### 3. Eager Load to Avoid N+1

```php
// Good ✅
$users = User::with('posts')->get();
foreach ($users as $user) {
    foreach ($user->posts as $post) {
        echo $post->title;
    }
}

// Bad ❌ - N+1 queries
$users = User::all();
foreach ($users as $user) {
    foreach ($user->posts as $post) {  // Query per user
        echo $post->title;
    }
}
```

### 4. Use Polymorphic for Shared Behavior

```php
// Good ✅
#[MorphMany(Comment::class, name: 'commentable')]
public array $comments;

// Works for Post, Video, etc.
```

### 5. Define Both Sides of Relationships

```php
// Good ✅
class Post {
    #[BelongsTo(User::class)]
    public User $user;
}

class User {
    #[HasMany(Post::class)]
    public array $posts;
}
```

## Next Steps

- [Validation](validation.md)
- [Form Generation](form-generation.md)
- [Query Builder](../database/query-builder.md)
- [Migrations](../database/migrations.md)
