# Plugin Development

Learn how to develop professional NeoPhp plugins.

## Plugin Architecture

A well-structured plugin follows these principles:

1. **Self-Contained**: All plugin code in one directory
2. **Namespaced**: Unique namespace to avoid conflicts
3. **Configurable**: Settings for customization
4. **Extensible**: Provides hooks for other developers
5. **Documented**: Clear documentation and examples

## Development Workflow

### 1. Planning

Define your plugin's purpose:

```
Plugin: Blog
Purpose: Add blogging functionality
Features:
- Post management (CRUD)
- Categories and tags
- Comments system
- RSS feed
- SEO optimization
```

### 2. Scaffolding

Create plugin structure:

```bash
php neo make:plugin Blog --description="Blogging system"
```

### 3. Define Models

Create models with metadata:

```php
<?php

namespace Blog\Models;

use NeoPhp\Foundation\Model;
use NeoPhp\Metadata\Attributes\*;

#[Table('blog_posts')]
#[Timestamps]
#[SoftDeletes]
class Post extends Model
{
    #[ID]
    public int $id;
    
    #[Field(type: 'string', length: 255)]
    #[Validate(['required', 'string', 'max:255'])]
    public string $title;
    
    #[Field(type: 'string', length: 255, unique: true)]
    #[Validate(['required', 'alpha_dash', 'unique:blog_posts,slug'])]
    public string $slug;
    
    #[Field(type: 'longtext')]
    #[Validate(['required', 'string', 'min:100'])]
    public string $content;
    
    #[Field(type: 'integer', unsigned: true)]
    #[Index]
    public int $user_id;
    
    #[Field(type: 'enum', allowed: ['draft', 'published'], default: 'draft')]
    #[Validate(['required', 'in:draft,published'])]
    public string $status;
    
    #[BelongsTo(User::class, foreignKey: 'user_id')]
    public User $author;
    
    #[HasMany(Comment::class, foreignKey: 'post_id')]
    public array $comments;
    
    #[BelongsToMany(Tag::class, through: 'blog_post_tags')]
    public array $tags;
}
```

### 4. Create Migrations

```php
<?php

use NeoPhp\Database\Migration;
use NeoPhp\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
                
            $table->index(['status', 'created_at']);
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('blog_posts');
    }
};
```

### 5. Build Controllers

```php
<?php

namespace Blog\Controllers;

use Blog\Models\Post;
use NeoPhp\Foundation\Controller;
use NeoPhp\Validation\Validator;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('status', 'published')
            ->with(['author', 'tags'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return $this->view('blog::posts.index', compact('posts'));
    }
    
    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->with(['author', 'comments.user', 'tags'])
            ->firstOrFail();
        
        // Increment views
        $post->increment('view_count');
        
        // Fire hook
        do_action('blog.post_viewed', $post);
        
        return $this->view('blog::posts.show', compact('post'));
    }
    
    public function create()
    {
        return $this->view('blog::posts.create');
    }
    
    public function store()
    {
        $rules = Validator::fromModel(Post::class);
        $validator = new Validator($this->request->all(), $rules);
        
        if ($validator->fails()) {
            return $this->back()->withErrors($validator->errors());
        }
        
        $post = Post::create($validator->validated() + [
            'user_id' => auth()->id()
        ]);
        
        // Fire hook
        do_action('blog.post_created', $post);
        
        return $this->redirect("/blog/{$post->slug}")
            ->with('success', 'Post created successfully');
    }
}
```

### 6. Define Routes

```php
<?php

use Blog\Controllers\{PostController, CommentController};

// Public routes
Route::get('/blog', [PostController::class, 'index']);
Route::get('/blog/{slug}', [PostController::class, 'show']);

// Comments
Route::post('/blog/{slug}/comments', [CommentController::class, 'store'])
    ->middleware('auth');

// Admin routes
Route::middleware(['auth', 'admin'])->group(function() {
    Route::get('/blog/create', [PostController::class, 'create']);
    Route::post('/blog', [PostController::class, 'store']);
    Route::get('/blog/{id}/edit', [PostController::class, 'edit']);
    Route::put('/blog/{id}', [PostController::class, 'update']);
    Route::delete('/blog/{id}', [PostController::class, 'destroy']);
});
```

### 7. Create Services

```php
<?php

namespace Blog\Services;

use Blog\Models\Post;

class BlogService
{
    /**
     * Get published posts
     */
    public function getPublishedPosts(int $perPage = 10)
    {
        return Post::where('status', 'published')
            ->with(['author', 'tags'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Get related posts
     */
    public function getRelatedPosts(Post $post, int $limit = 5): array
    {
        $tagIds = $post->tags->pluck('id')->toArray();
        
        return Post::where('id', '!=', $post->id)
            ->where('status', 'published')
            ->whereHas('tags', function($query) use ($tagIds) {
                $query->whereIn('tag_id', $tagIds);
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }
    
    /**
     * Generate sitemap
     */
    public function generateSitemap(): string
    {
        $posts = Post::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($posts as $post) {
            $xml .= '<url>';
            $xml .= '<loc>' . url("/blog/{$post->slug}") . '</loc>';
            $xml .= '<lastmod>' . $post->updated_at . '</lastmod>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Generate RSS feed
     */
    public function generateRssFeed(): string
    {
        $posts = Post::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0">';
        $xml .= '<channel>';
        $xml .= '<title>' . config('blog.title') . '</title>';
        $xml .= '<link>' . url('/blog') . '</link>';
        $xml .= '<description>' . config('blog.description') . '</description>';
        
        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title>' . htmlspecialchars($post->title) . '</title>';
            $xml .= '<link>' . url("/blog/{$post->slug}") . '</link>';
            $xml .= '<description>' . htmlspecialchars($post->excerpt) . '</description>';
            $xml .= '<pubDate>' . date('r', strtotime($post->created_at)) . '</pubDate>';
            $xml .= '</item>';
        }
        
        $xml .= '</channel>';
        $xml .= '</rss>';
        
        return $xml;
    }
}
```

### 8. Add CLI Commands

```php
<?php

namespace Blog\Commands;

use Blog\Models\Post;
use NeoPhp\Foundation\Console\Command;

class GenerateSitemapCommand extends Command
{
    protected string $signature = 'blog:sitemap';
    protected string $description = 'Generate blog sitemap';
    
    public function handle(): int
    {
        $this->info('Generating sitemap...');
        
        $service = app(BlogService::class);
        $xml = $service->generateSitemap();
        
        file_put_contents(public_path('sitemap-blog.xml'), $xml);
        
        $count = Post::where('status', 'published')->count();
        
        $this->success("Sitemap generated with {$count} posts");
        
        return 0;
    }
}
```

### 9. Implement Hooks

```php
<?php

namespace Blog;

use NeoPhp\Foundation\Plugin\AbstractPlugin;

class Plugin extends AbstractPlugin
{
    public function boot(): void
    {
        // Register hooks
        $this->registerHooks();
    }
    
    protected function registerHooks(): void
    {
        // Actions
        $this->addAction('user.deleted', [$this, 'onUserDeleted']);
        $this->addAction('blog.post_created', [$this, 'onPostCreated']);
        
        // Filters
        $this->addFilter('blog.post_content', [$this, 'addReadMore'], 10, 2);
        $this->addFilter('blog.post_excerpt', [$this, 'formatExcerpt']);
    }
    
    /**
     * Handle user deletion
     */
    public function onUserDeleted($user): void
    {
        // Delete or reassign user's posts
        Post::where('user_id', $user->id)->delete();
    }
    
    /**
     * Handle post created
     */
    public function onPostCreated($post): void
    {
        // Send notification
        Mail::to(config('admin.email'))
            ->send(new NewPostNotification($post));
        
        // Clear cache
        Cache::tags(['blog'])->flush();
    }
    
    /**
     * Add read more link
     */
    public function addReadMore(string $content, Post $post): string
    {
        if (strlen($content) > 500) {
            $excerpt = substr($content, 0, 500) . '...';
            $readMore = '<a href="/blog/' . $post->slug . '">Read More</a>';
            return $excerpt . $readMore;
        }
        return $content;
    }
    
    /**
     * Format excerpt
     */
    public function formatExcerpt(string $excerpt): string
    {
        // Strip tags
        $excerpt = strip_tags($excerpt);
        
        // Limit length
        if (strlen($excerpt) > 200) {
            $excerpt = substr($excerpt, 0, 200) . '...';
        }
        
        return $excerpt;
    }
}
```

## Testing Your Plugin

### Unit Tests

```php
<?php

namespace Blog\Tests;

use Blog\Models\Post;
use NeoPhp\Testing\TestCase;

class PostTest extends TestCase
{
    public function testCreatePost(): void
    {
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'This is a test post.',
            'user_id' => 1,
            'status' => 'draft'
        ]);
        
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post', $post->title);
    }
    
    public function testPublishPost(): void
    {
        $post = Post::factory()->create(['status' => 'draft']);
        
        $post->status = 'published';
        $post->save();
        
        $this->assertEquals('published', $post->status);
    }
}
```

### Feature Tests

```php
<?php

namespace Blog\Tests\Feature;

use Blog\Models\Post;
use NeoPhp\Testing\TestCase;

class BlogTest extends TestCase
{
    public function testViewBlogIndex(): void
    {
        Post::factory()->count(3)->create(['status' => 'published']);
        
        $response = $this->get('/blog');
        
        $response->assertStatus(200);
        $response->assertSee('Blog');
    }
    
    public function testViewPost(): void
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'slug' => 'test-post'
        ]);
        
        $response = $this->get("/blog/test-post");
        
        $response->assertStatus(200);
        $response->assertSee($post->title);
    }
    
    public function testCreatePost(): void
    {
        $this->actingAs($this->adminUser());
        
        $data = [
            'title' => 'New Post',
            'slug' => 'new-post',
            'content' => 'This is a new post.',
            'status' => 'draft'
        ];
        
        $response = $this->post('/blog', $data);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('blog_posts', ['slug' => 'new-post']);
    }
}
```

## Plugin Configuration

### Configuration File

```php
<?php

// config/blog.php

return [
    // General settings
    'title' => env('BLOG_TITLE', 'My Blog'),
    'description' => env('BLOG_DESCRIPTION', 'A blog about...'),
    'posts_per_page' => env('BLOG_POSTS_PER_PAGE', 10),
    
    // Comments
    'comments_enabled' => env('BLOG_COMMENTS_ENABLED', true),
    'comment_moderation' => env('BLOG_COMMENT_MODERATION', true),
    
    // SEO
    'seo_enabled' => true,
    'sitemap_enabled' => true,
    'rss_enabled' => true,
    
    // Social sharing
    'social_sharing' => [
        'facebook' => true,
        'twitter' => true,
        'linkedin' => true,
    ],
    
    // Image settings
    'featured_image' => [
        'width' => 1200,
        'height' => 630,
        'quality' => 90,
    ]
];
```

## Plugin Assets

### CSS

```css
/* assets/css/blog.css */

.blog-container {
    max-width: 800px;
    margin: 0 auto;
}

.post-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.post-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
}

.post-meta {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
}

.post-excerpt {
    line-height: 1.6;
}
```

### JavaScript

```javascript
// assets/js/blog.js

document.addEventListener('DOMContentLoaded', function() {
    // Social sharing
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.dataset.url;
            const platform = this.dataset.platform;
            
            let shareUrl;
            if (platform === 'facebook') {
                shareUrl = `https://facebook.com/sharer.php?u=${url}`;
            } else if (platform === 'twitter') {
                shareUrl = `https://twitter.com/intent/tweet?url=${url}`;
            }
            
            window.open(shareUrl, '_blank', 'width=600,height=400');
        });
    });
});
```

## Best Practices

### 1. Use Semantic Versioning

```json
{
    "version": "1.2.3"
}

// 1 = Major (breaking changes)
// 2 = Minor (new features)
// 3 = Patch (bug fixes)
```

### 2. Provide Hooks

```php
// Good ✅ - Allows extensibility
do_action('blog.post_created', $post);
$content = apply_filters('blog.post_content', $content, $post);

// Bad ❌ - No way to extend
```

### 3. Handle Errors Gracefully

```php
// Good ✅
try {
    $post = Post::findOrFail($id);
} catch (ModelNotFoundException $e) {
    return $this->notFound('Post not found');
}

// Bad ❌
$post = Post::find($id);  // May return null
```

### 4. Cache When Possible

```php
// Good ✅
$posts = Cache::remember('blog.recent', 3600, function() {
    return Post::recent()->get();
});

// Bad ❌
$posts = Post::recent()->get();  // Every request
```

### 5. Document Everything

```php
/**
 * Get published blog posts
 *
 * @param int $perPage Number of posts per page
 * @return \Illuminate\Pagination\LengthAwarePaginator
 */
public function getPublishedPosts(int $perPage = 10)
{
    // ...
}
```

## Publishing Your Plugin

### 1. Create README

```markdown
# Blog Plugin

Complete blogging system for NeoPhp.

## Features

- Post management (CRUD)
- Categories and tags
- Comments system
- RSS feed
- SEO optimization

## Installation

\`\`\`bash
php neo plugin:install blog
php neo plugin:activate blog
\`\`\`

## Configuration

Copy `.env.example` to `.env` and configure.

## Usage

See documentation at https://docs.example.com
```

### 2. Add License

Include appropriate license file (MIT, GPL, etc.)

### 3. Version Control

Use Git for version control and semantic versioning.

### 4. Submit to Marketplace

Package and submit to NeoPhp plugin marketplace.

## Next Steps

- [Plugin API](plugin-api.md)
- [Testing](../testing/introduction.md)
- [Deployment](../deployment/introduction.md)
