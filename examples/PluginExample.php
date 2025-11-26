<?php

/**
 * Example: Creating a Plugin for NeoPhp
 */

// ===================================
// Plugin Structure:
// plugins/
//   └── BlogPlugin/
//       ├── Plugin.php
//       ├── Controllers/
//       │   └── BlogController.php
//       ├── Models/
//       │   └── Post.php
//       └── views/
//           └── blog/
//               └── index.blade.php
// ===================================

namespace Plugins\BlogPlugin;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Plugin\HookManager;
use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Metadata\{Table, Field, BelongsTo};

/**
 * Blog Plugin Main Class
 */
class BlogPlugin extends Plugin
{
    protected string $name = 'Blog Plugin';
    protected string $version = '1.0.0';
    protected string $description = 'A simple blog plugin for NeoPhp';
    protected array $dependencies = []; // No dependencies

    /**
     * Install plugin - create tables
     */
    public function install(): void
    {
        $db = app('db');
        
        // Create posts table
        $db->execute("
            CREATE TABLE IF NOT EXISTS blog_posts (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                content TEXT,
                excerpt VARCHAR(500),
                author_id INT NOT NULL,
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                published_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // Create categories table
        $db->execute("
            CREATE TABLE IF NOT EXISTS blog_categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create post-category pivot table
        $db->execute("
            CREATE TABLE IF NOT EXISTS blog_post_categories (
                post_id INT NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY (post_id, category_id),
                FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
            )
        ");

        logger()->info('Blog plugin installed successfully');
    }

    /**
     * Uninstall plugin - cleanup
     */
    public function uninstall(): void
    {
        $db = app('db');
        
        // Drop tables
        $db->execute("DROP TABLE IF EXISTS blog_post_categories");
        $db->execute("DROP TABLE IF EXISTS blog_posts");
        $db->execute("DROP TABLE IF EXISTS blog_categories");

        logger()->info('Blog plugin uninstalled successfully');
    }

    /**
     * Boot plugin - register routes, hooks, services
     */
    public function boot(): void
    {
        // Register routes
        $this->registerRoutes();

        // Register hooks
        $this->registerHooks();

        // Register service provider
        $this->registerServiceProvider();

        // Add admin menu
        $this->registerAdminMenu();

        logger()->info('Blog plugin booted');
    }

    /**
     * Register plugin routes
     */
    protected function registerRoutes(): void
    {
        // Public routes
        Route::get('/blog', [BlogController::class, 'index']);
        Route::get('/blog/category/{slug}', [BlogController::class, 'category']);
        Route::get('/blog/{slug}', [BlogController::class, 'show']);

        // Admin routes
        Route::group('/admin/blog', [
            'middleware' => ['auth', 'admin'],
            'controller' => AdminBlogController::class
        ], function() {
            Route::get('/', 'index');
            Route::get('/create', 'create');
            Route::post('/', 'store');
            Route::get('/{id}/edit', 'edit');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    }

    /**
     * Register plugin hooks
     */
    protected function registerHooks(): void
    {
        // Action: After post created
        HookManager::addAction('blog.post.created', function($post) {
            // Clear cache
            cache()->delete('blog:latest');
            cache()->delete('blog:popular');
            
            // Log
            logger()->info("Blog post created: {$post['title']}");
            
            // Send notification
            // NotificationService::send('New blog post published');
        });

        // Filter: Modify post content before display
        HookManager::addFilter('blog.post.content', function($content) {
            // Add reading time
            $words = str_word_count(strip_tags($content));
            $readTime = ceil($words / 200); // 200 words per minute
            
            return "<p class='read-time'>Read time: {$readTime} min</p>" . $content;
        });

        // Filter: Add custom fields to post
        HookManager::addFilter('blog.post.fields', function($fields) {
            $fields['custom_field'] = 'Custom value';
            return $fields;
        });
    }

    /**
     * Register service provider
     */
    protected function registerServiceProvider(): void
    {
        provider()->register(BlogServiceProvider::class);
    }

    /**
     * Register admin menu
     */
    protected function registerAdminMenu(): void
    {
        // Add menu hook
        HookManager::addAction('admin.menu', function($menu) {
            $menu->add([
                'title' => 'Blog',
                'icon' => 'newspaper',
                'url' => '/admin/blog',
                'permission' => 'manage-blog',
                'submenu' => [
                    ['title' => 'All Posts', 'url' => '/admin/blog'],
                    ['title' => 'Add New', 'url' => '/admin/blog/create'],
                    ['title' => 'Categories', 'url' => '/admin/blog/categories'],
                ]
            ]);
        });
    }
}

/**
 * Blog Post Model with Metadata
 */
#[Table('blog_posts')]
class Post
{
    #[Field('id', type: 'integer', primary: true, autoIncrement: true)]
    public int $id;

    #[Field('title',
        type: 'string',
        length: 255,
        validation: ['required', 'max:255'],
        searchable: true,
        sortable: true
    )]
    public string $title;

    #[Field('slug',
        type: 'string',
        length: 255,
        unique: true,
        validation: ['required', 'unique:blog_posts']
    )]
    public string $slug;

    #[Field('content',
        type: 'text',
        validation: ['required'],
        inputType: 'textarea'
    )]
    public string $content;

    #[Field('excerpt',
        type: 'string',
        length: 500,
        nullable: true,
        inputType: 'textarea'
    )]
    public ?string $excerpt;

    #[Field('author_id',
        type: 'integer',
        validation: ['required', 'exists:users,id']
    )]
    public int $author_id;

    #[Field('status',
        type: 'enum',
        enum: ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'],
        default: 'draft',
        filterable: true
    )]
    public string $status;

    #[BelongsTo(model: User::class, foreignKey: 'author_id')]
    public function author() {}
}

/**
 * Blog Controller
 */
class BlogController
{
    public function index()
    {
        // Get published posts
        $posts = Post::where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->paginate(10);

        // Apply filter hook
        foreach ($posts->items() as $post) {
            $post->content = apply_filters('blog.post.content', $post->content);
        }

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$post) {
            abort(404);
        }

        // Apply filters
        $post->content = apply_filters('blog.post.content', $post->content);
        
        // Increment views (example)
        do_action('blog.post.viewed', $post);

        return view('blog.show', compact('post'));
    }
}

/**
 * Admin Blog Controller - Using Metadata
 */
class AdminBlogController
{
    protected $metadata;
    protected $formBuilder;

    public function __construct()
    {
        $this->metadata = metadata();
        $this->formBuilder = form();
    }

    public function index()
    {
        $posts = Post::paginate(25);
        $metadata = $this->metadata->getModelMetadata(Post::class);
        
        return view('admin.blog.index', [
            'posts' => $posts,
            'metadata' => $metadata
        ]);
    }

    public function create()
    {
        // Auto-generate form from metadata
        $form = $this->formBuilder->make(Post::class, [
            'action' => '/admin/blog',
            'method' => 'POST'
        ]);

        return view('admin.blog.create', compact('form'));
    }

    public function store()
    {
        // Auto-validate from metadata
        $rules = $this->metadata->getValidationRules(Post::class);
        $validator = validator(request()->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        
        // Generate slug
        if (!isset($data['slug'])) {
            $data['slug'] = str_slug($data['title']);
        }

        // Set author
        $data['author_id'] = auth()->id();

        // Create post
        $post = Post::create($data);

        // Trigger hook
        do_action('blog.post.created', $post);

        return redirect('/admin/blog')->with('success', 'Post created successfully');
    }
}

/**
 * Blog Service Provider
 */
class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->singleton('blog', function($app) {
            return new BlogService($app->make('db'));
        });
    }

    public function boot(): void
    {
        // Register view paths
        view()->addPath(__DIR__ . '/views');
        
        // Register permissions
        if (function_exists('register_permission')) {
            register_permission('manage-blog', 'Manage Blog Posts');
        }
    }
}

// ===================================
// Usage in Application
// ===================================

// 1. Install plugin
plugin()->install('Blog Plugin');

// 2. Activate plugin
plugin()->activate('Blog Plugin');

// 3. Use plugin hooks in your code
hook_action('blog.post.created', function($post) {
    // Your custom logic when post created
    mail()->send(['editor@example.com'], 'New Post', "New post: {$post->title}");
});

// 4. Modify plugin behavior with filters
hook_filter('blog.post.content', function($content) {
    // Add table of contents
    return '<div class="toc">Table of Contents</div>' . $content;
}, 20); // Priority 20 (runs after default filter)
