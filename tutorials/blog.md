# Building a Blog Application

Learn how to build a complete blog application with NeoPhP.

## Project Setup

### Initialize Project

```bash
php neo new blog-app
cd blog-app
```

### Configure Database

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=
```

## Database Design

### Create Migrations

```bash
php neo make:migration create_posts_table
php neo make:migration create_categories_table
php neo make:migration create_comments_table
php neo make:migration create_tags_table
php neo make:migration create_post_tag_table
```

### Posts Migration

```php
<?php

use NeoPhp\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('posts', function($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamps();
            
            $table->index(['status', 'published_at']);
            $table->fulltext(['title', 'content']);
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('posts');
    }
};
```

### Categories Migration

```php
public function up(): void
{
    $this->schema->create('categories', function($table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->integer('posts_count')->default(0);
        $table->timestamps();
    });
}
```

### Comments Migration

```php
public function up(): void
{
    $this->schema->create('comments', function($table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained();
        $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
        $table->text('content');
        $table->boolean('approved')->default(false);
        $table->timestamps();
        
        $table->index(['post_id', 'approved']);
    });
}
```

### Tags & Pivot Migration

```php
// Tags table
public function up(): void
{
    $this->schema->create('tags', function($table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });
}

// Post-Tag pivot table
public function up(): void
{
    $this->schema->create('post_tag', function($table) {
        $table->foreignId('post_id')->constrained()->onDelete('cascade');
        $table->foreignId('tag_id')->constrained()->onDelete('cascade');
        $table->primary(['post_id', 'tag_id']);
    });
}
```

Run migrations:

```bash
php neo migrate
```

## Models

### Post Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Post extends Model
{
    protected array $fillable = [
        'title', 'slug', 'excerpt', 'content',
        'featured_image', 'status', 'published_at',
        'user_id', 'category_id'
    ];
    
    protected array $casts = [
        'published_at' => 'datetime',
        'views_count' => 'integer'
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    
    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }
    
    public function scopePopular($query, int $limit = 5)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }
    
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('published_at', 'desc')->limit($limit);
    }
    
    // Methods
    public function isPublished(): bool
    {
        return $this->status === 'published' 
            && $this->published_at <= now();
    }
    
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
    
    public function getReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return ceil($wordCount / 200); // 200 words per minute
    }
}
```

### Category Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Category extends Model
{
    protected array $fillable = ['name', 'slug', 'description'];
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->published();
    }
}
```

### Comment Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Comment extends Model
{
    protected array $fillable = [
        'post_id', 'user_id', 'parent_id', 'content', 'approved'
    ];
    
    protected array $casts = [
        'approved' => 'boolean'
    ];
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
    
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }
}
```

### Tag Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Tag extends Model
{
    protected array $fillable = ['name', 'slug'];
    
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
```

## Controllers

### PostController

```php
<?php

namespace App\Controllers;

use App\Models\{Post, Category, Tag};
use NeoPhp\Http\Request;

class PostController
{
    public function index(Request $request)
    {
        $posts = Post::with(['user', 'category', 'tags'])
            ->published()
            ->when($request->category, function($query, $category) {
                $query->whereHas('category', fn($q) => $q->where('slug', $category));
            })
            ->when($request->tag, function($query, $tag) {
                $query->whereHas('tags', fn($q) => $q->where('slug', $tag));
            })
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->paginate(12);
        
        return view('posts.index', [
            'posts' => $posts,
            'categories' => Category::withCount('posts')->get(),
            'popularPosts' => Post::published()->popular()->get()
        ]);
    }
    
    public function show(string $slug)
    {
        $post = Post::with(['user', 'category', 'tags', 'comments.user'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
        
        $post->incrementViews();
        
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();
        
        return view('posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }
    
    public function create()
    {
        return view('posts.create', [
            'categories' => Category::all(),
            'tags' => Tag::all()
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'excerpt' => 'nullable|max:500',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published'
        ]);
        
        $validated['user_id'] = auth()->id();
        $validated['slug'] = $this->generateSlug($validated['title']);
        
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }
        
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')
                ->store('posts', 'public');
        }
        
        $post = Post::create($validated);
        
        if (!empty($validated['tags'])) {
            $post->tags()->attach($validated['tags']);
        }
        
        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post created successfully');
    }
    
    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        
        return view('posts.edit', [
            'post' => $post,
            'categories' => Category::all(),
            'tags' => Tag::all()
        ]);
    }
    
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        
        $validated = $request->validate([
            'title' => 'required|max:255',
            'excerpt' => 'nullable|max:500',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published'
        ]);
        
        if ($validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        }
        
        if ($request->hasFile('featured_image')) {
            Storage::disk('public')->delete($post->featured_image);
            $validated['featured_image'] = $request->file('featured_image')
                ->store('posts', 'public');
        }
        
        $post->update($validated);
        $post->tags()->sync($validated['tags'] ?? []);
        
        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post updated successfully');
    }
    
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        Storage::disk('public')->delete($post->featured_image);
        $post->delete();
        
        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully');
    }
    
    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Post::where('slug', 'like', "{$slug}%")->count();
        
        return $count ? "{$slug}-{$count}" : $slug;
    }
}
```

### CommentController

```php
<?php

namespace App\Controllers;

use App\Models\{Post, Comment};
use NeoPhp\Http\Request;

class CommentController
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);
        
        $validated['user_id'] = auth()->id();
        $validated['post_id'] = $post->id;
        $validated['approved'] = auth()->user()->isTrusted(); // Auto-approve trusted users
        
        $comment = Comment::create($validated);
        
        return back()->with('success', 'Comment posted successfully');
    }
    
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        
        $comment->delete();
        
        return back()->with('success', 'Comment deleted');
    }
}
```

## Views

### Posts Index (posts/index.blade.php)

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h1>Blog Posts</h1>
            
            @if($posts->isEmpty())
                <p>No posts found.</p>
            @else
                @foreach($posts as $post)
                    <article class="post-card">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}">
                        @endif
                        
                        <h2>
                            <a href="{{ route('posts.show', $post->slug) }}">
                                {{ $post->title }}
                            </a>
                        </h2>
                        
                        <div class="post-meta">
                            <span>By {{ $post->user->name }}</span>
                            <span>{{ $post->published_at->format('M d, Y') }}</span>
                            <span>{{ $post->comments->count() }} comments</span>
                            <span>{{ $post->views_count }} views</span>
                        </div>
                        
                        <p>{{ $post->excerpt }}</p>
                        
                        <div class="post-tags">
                            @foreach($post->tags as $tag)
                                <a href="{{ route('posts.index', ['tag' => $tag->slug]) }}" class="tag">
                                    {{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                        
                        <a href="{{ route('posts.show', $post->slug) }}" class="read-more">
                            Read More
                        </a>
                    </article>
                @endforeach
                
                {{ $posts->links() }}
            @endif
        </div>
        
        <div class="col-md-4">
            <div class="sidebar">
                <div class="widget">
                    <h3>Categories</h3>
                    <ul>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('posts.index', ['category' => $category->slug]) }}">
                                    {{ $category->name }} ({{ $category->posts_count }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="widget">
                    <h3>Popular Posts</h3>
                    <ul>
                        @foreach($popularPosts as $post)
                            <li>
                                <a href="{{ route('posts.show', $post->slug) }}">
                                    {{ $post->title }}
                                </a>
                                <small>{{ $post->views_count }} views</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### Post Show (posts/show.blade.php)

```blade
@extends('layouts.app')

@section('content')
<article class="post">
    @if($post->featured_image)
        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="featured-image">
    @endif
    
    <h1>{{ $post->title }}</h1>
    
    <div class="post-meta">
        <span>By {{ $post->user->name }}</span>
        <span>{{ $post->published_at->format('M d, Y') }}</span>
        <span>{{ $post->getReadingTime() }} min read</span>
        <span>{{ $post->views_count }} views</span>
    </div>
    
    <div class="post-tags">
        @foreach($post->tags as $tag)
            <a href="{{ route('posts.index', ['tag' => $tag->slug]) }}" class="tag">
                {{ $tag->name }}
            </a>
        @endforeach
    </div>
    
    <div class="post-content">
        {!! $post->content !!}
    </div>
    
    @if($relatedPosts->isNotEmpty())
        <div class="related-posts">
            <h3>Related Posts</h3>
            <div class="row">
                @foreach($relatedPosts as $related)
                    <div class="col-md-4">
                        <a href="{{ route('posts.show', $related->slug) }}">
                            {{ $related->title }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <div class="comments">
        <h3>Comments ({{ $post->comments->count() }})</h3>
        
        @auth
            <form method="POST" action="{{ route('comments.store', $post) }}">
                @csrf
                <textarea name="content" rows="4" placeholder="Leave a comment..." required></textarea>
                <button type="submit">Post Comment</button>
            </form>
        @else
            <p><a href="{{ route('login') }}">Login</a> to leave a comment.</p>
        @endauth
        
        @foreach($post->comments()->whereNull('parent_id')->approved()->get() as $comment)
            @include('comments.comment', ['comment' => $comment])
        @endforeach
    </div>
</article>
@endsection
```

## Routes

```php
<?php

use App\Controllers\{PostController, CommentController};

// Public routes
Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show');

// Authenticated routes
Route::middleware('auth')->group(function() {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});
```

## Seeders

```php
<?php

use NeoPhp\Database\Seeder;
use App\Models\{User, Category, Tag, Post};

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@blog.com',
            'password' => Hash::make('password')
        ]);
        
        // Create categories
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle'],
            ['name' => 'Travel', 'slug' => 'travel'],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
        
        // Create tags
        $tags = ['PHP', 'NeoPhP', 'Web Development', 'Tutorial'];
        foreach ($tags as $tag) {
            Tag::create(['name' => $tag, 'slug' => Str::slug($tag)]);
        }
        
        // Create posts
        Post::factory()->count(20)->create();
    }
}
```

## Next Steps

- Add search functionality with Elasticsearch
- Implement post bookmarking
- Add social sharing buttons
- Create RSS feed
- Add email notifications for new comments

## Resources

- [Query Builder](../database/query-builder.md)
- [Validation](../metadata/validation.md)
- [File Uploads](../core/file-uploads.md)
