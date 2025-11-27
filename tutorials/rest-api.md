# Building a REST API

Create a professional REST API with authentication, versioning, and documentation.

## Project Setup

### Initialize API Project

```bash
php neo new api-project
cd api-project
```

### Configure for API

Update `.env`:

```env
APP_URL=http://localhost:8000/api
API_VERSION=v1
API_RATE_LIMIT=60
```

## API Structure

```
app/
├── Controllers/
│   └── Api/
│       └── V1/
│           ├── AuthController.php
│           ├── UserController.php
│           ├── PostController.php
│           └── CommentController.php
├── Resources/
│   ├── UserResource.php
│   ├── PostResource.php
│   └── CommentResource.php
├── Requests/
│   ├── LoginRequest.php
│   ├── RegisterRequest.php
│   └── PostRequest.php
└── Middleware/
    ├── ApiAuthentication.php
    └── ApiVersioning.php
```

## Authentication System

### API Token Migration

```bash
php neo make:migration create_api_tokens_table
```

```php
<?php

use NeoPhp\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('api_tokens', function($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'token']);
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('api_tokens');
    }
};
```

### User Model with API Tokens

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;
use NeoPhp\Support\Str;

class User extends Model
{
    protected array $fillable = ['name', 'email', 'password'];
    
    protected array $hidden = ['password'];
    
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }
    
    public function createToken(string $name, array $abilities = ['*']): ApiToken
    {
        $token = Str::random(64);
        
        return $this->apiTokens()->create([
            'name' => $name,
            'token' => hash('sha256', $token),
            'abilities' => json_encode($abilities),
        ]);
    }
    
    public function tokens()
    {
        return $this->apiTokens;
    }
}
```

### API Token Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class ApiToken extends Model
{
    protected array $fillable = [
        'user_id', 'name', 'token', 'abilities', 'last_used_at', 'expires_at'
    ];
    
    protected array $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function can(string $ability): bool
    {
        if (in_array('*', $this->abilities ?? [])) {
            return true;
        }
        
        return in_array($ability, $this->abilities ?? []);
    }
    
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
    
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
```

### Authentication Middleware

```php
<?php

namespace App\Middleware;

use App\Models\ApiToken;
use Closure;

class ApiAuthentication
{
    public function handle($request, Closure $next)
    {
        $token = $this->extractToken($request);
        
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $apiToken = ApiToken::where('token', hash('sha256', $token))
            ->with('user')
            ->first();
        
        if (!$apiToken || $apiToken->isExpired()) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }
        
        $apiToken->recordUsage();
        
        $request->setUser($apiToken->user);
        $request->attributes->set('api_token', $apiToken);
        
        return $next($request);
    }
    
    private function extractToken($request): ?string
    {
        $header = $request->header('Authorization');
        
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }
        
        return substr($header, 7);
    }
}
```

## API Resources

### User Resource

```php
<?php

namespace App\Resources;

use NeoPhp\Http\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

### Post Resource

```php
<?php

namespace App\Resources;

use NeoPhp\Http\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($this->isDetailView(), $this->content),
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'author' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'views_count' => $this->views_count,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
    
    private function isDetailView(): bool
    {
        return request()->route()->getName() === 'api.posts.show';
    }
}
```

### Paginated Resource

```php
<?php

namespace App\Resources;

use NeoPhp\Http\ResourceCollection;

class PostCollection extends ResourceCollection
{
    public function toArray(): array
    {
        return [
            'data' => PostResource::collection($this->collection),
            'meta' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ]
        ];
    }
}
```

## Request Validation

### Login Request

```php
<?php

namespace App\Requests;

use NeoPhp\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ];
    }
    
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email',
            'password.required' => 'Password is required',
        ];
    }
}
```

### Post Request

```php
<?php

namespace App\Requests;

use NeoPhp\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-post');
    }
    
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published',
        ];
    }
}
```

## Controllers

### AuthController

```php
<?php

namespace App\Controllers\Api\V1;

use App\Models\User;
use App\Requests\{LoginRequest, RegisterRequest};
use App\Resources\UserResource;
use NeoPhp\Support\Facades\Hash;

class AuthController
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        $token = $user->createToken('auth-token');
        
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token->token,
        ], 201);
    }
    
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }
        
        $token = $user->createToken('auth-token');
        
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token->token,
        ]);
    }
    
    public function logout($request)
    {
        $token = $request->attributes->get('api_token');
        $token->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    
    public function me($request)
    {
        return new UserResource($request->user());
    }
}
```

### PostController

```php
<?php

namespace App\Controllers\Api\V1;

use App\Models\Post;
use App\Requests\PostRequest;
use App\Resources\{PostResource, PostCollection};
use NeoPhp\Http\Request;

class PostController
{
    public function index(Request $request)
    {
        $posts = Post::with(['user', 'category', 'tags'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->category, function($q, $category) {
                $q->whereHas('category', fn($query) => $query->where('slug', $category));
            })
            ->when($request->search, function($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->sort, function($q, $sort) {
                match($sort) {
                    'latest' => $q->latest(),
                    'oldest' => $q->oldest(),
                    'popular' => $q->orderBy('views_count', 'desc'),
                    default => $q->latest()
                };
            })
            ->paginate($request->per_page ?? 15);
        
        return new PostCollection($posts);
    }
    
    public function show(string $id)
    {
        $post = Post::with(['user', 'category', 'tags', 'comments'])
            ->findOrFail($id);
        
        $post->incrementViews();
        
        return new PostResource($post);
    }
    
    public function store(PostRequest $request)
    {
        $post = Post::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id,
            'status' => $request->status,
            'published_at' => $request->status === 'published' ? now() : null,
        ]);
        
        if ($request->tags) {
            $post->tags()->attach($request->tags);
        }
        
        return new PostResource($post->load(['user', 'category', 'tags']));
    }
    
    public function update(PostRequest $request, Post $post)
    {
        $this->authorize('update', $post);
        
        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'category_id' => $request->category_id,
            'status' => $request->status,
        ]);
        
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }
        
        return new PostResource($post->fresh(['user', 'category', 'tags']));
    }
    
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        $post->delete();
        
        return response()->json(null, 204);
    }
}
```

### UserController

```php
<?php

namespace App\Controllers\Api\V1;

use App\Models\User;
use App\Resources\UserResource;
use NeoPhp\Http\Request;

class UserController
{
    public function index(Request $request)
    {
        $users = User::when($request->search, function($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate($request->per_page ?? 15);
        
        return UserResource::collection($users);
    }
    
    public function show(User $user)
    {
        return new UserResource($user);
    }
    
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);
        
        $user->update($validated);
        
        return new UserResource($user);
    }
    
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        $user->delete();
        
        return response()->json(null, 204);
    }
}
```

## Routes

### API Routes (routes/api.php)

```php
<?php

use App\Controllers\Api\V1\{AuthController, PostController, UserController};

// API Version 1
Route::prefix('v1')->group(function() {
    
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    
    // Protected routes
    Route::middleware('api.auth')->group(function() {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        
        // Posts
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);
        
        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});
```

## Error Handling

### API Exception Handler

```php
<?php

namespace App\Exceptions;

use Exception;
use NeoPhp\Http\Response;

class ApiExceptionHandler
{
    public function render(Exception $exception): Response
    {
        $statusCode = method_exists($exception, 'getStatusCode') 
            ? $exception->getStatusCode() 
            : 500;
        
        $response = [
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $statusCode,
            ]
        ];
        
        if (config('app.debug')) {
            $response['error']['trace'] = $exception->getTrace();
        }
        
        return response()->json($response, $statusCode);
    }
}
```

## Rate Limiting

### Rate Limit Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use NeoPhp\Cache\Facades\Cache;

class RateLimitMiddleware
{
    public function handle($request, Closure $next, int $maxAttempts = 60)
    {
        $key = $this->resolveKey($request);
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'error' => 'Too many requests',
                'retry_after' => Cache::ttl($key)
            ], 429);
        }
        
        Cache::put($key, $attempts + 1, 60);
        
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        
        return $response;
    }
    
    private function resolveKey($request): string
    {
        $user = $request->user();
        $identifier = $user ? "user:{$user->id}" : "ip:{$request->ip()}";
        
        return "rate_limit:{$identifier}";
    }
}
```

## API Versioning

### Version Middleware

```php
<?php

namespace App\Middleware;

use Closure;

class ApiVersionMiddleware
{
    public function handle($request, Closure $next)
    {
        $version = $request->header('API-Version', 'v1');
        
        if (!in_array($version, ['v1', 'v2'])) {
            return response()->json([
                'error' => 'Unsupported API version'
            ], 400);
        }
        
        $request->attributes->set('api_version', $version);
        
        return $next($request);
    }
}
```

## API Documentation

### OpenAPI Specification (openapi.yaml)

```yaml
openapi: 3.0.0
info:
  title: NeoPhP API
  version: 1.0.0
  description: RESTful API for NeoPhP application

servers:
  - url: http://localhost:8000/api/v1
    description: Development server

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    User:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
        email:
          type: string
        created_at:
          type: string
          format: date-time

    Post:
      type: object
      properties:
        id:
          type: integer
        title:
          type: string
        slug:
          type: string
        content:
          type: string
        status:
          type: string
          enum: [draft, published]

paths:
  /register:
    post:
      summary: Register a new user
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                name:
                  type: string
                email:
                  type: string
                password:
                  type: string
      responses:
        '201':
          description: User registered successfully

  /login:
    post:
      summary: Login user
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                email:
                  type: string
                password:
                  type: string
      responses:
        '200':
          description: Login successful

  /posts:
    get:
      summary: Get all posts
      parameters:
        - name: page
          in: query
          schema:
            type: integer
        - name: per_page
          in: query
          schema:
            type: integer
        - name: search
          in: query
          schema:
            type: string
      responses:
        '200':
          description: List of posts

    post:
      summary: Create a new post
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Post'
      responses:
        '201':
          description: Post created

  /posts/{id}:
    get:
      summary: Get post by ID
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Post details

    put:
      summary: Update post
      security:
        - bearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Post'
      responses:
        '200':
          description: Post updated

    delete:
      summary: Delete post
      security:
        - bearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '204':
          description: Post deleted
```

## Testing

### API Tests

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{User, Post};

class PostApiTest extends TestCase
{
    private User $user;
    private string $token;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $apiToken = $this->user->createToken('test-token');
        $this->token = $apiToken->token;
    }
    
    public function test_can_list_posts(): void
    {
        Post::factory()->count(5)->create();
        
        $response = $this->getJson('/api/v1/posts');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'content']
            ],
            'meta',
            'links'
        ]);
    }
    
    public function test_can_create_post(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/posts', [
                'title' => 'Test Post',
                'content' => 'Test content',
                'category_id' => 1,
                'status' => 'published'
            ]);
        
        $response->assertStatus(201);
        $response->assertJsonPath('data.title', 'Test Post');
    }
    
    public function test_cannot_create_post_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'content' => 'Test content'
        ]);
        
        $response->assertStatus(401);
    }
    
    public function test_rate_limiting_works(): void
    {
        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson('/api/v1/posts');
        }
        
        $response->assertStatus(429);
    }
    
    private function withToken(string $token): self
    {
        return $this->withHeader('Authorization', "Bearer {$token}");
    }
}
```

## API Client Example

### JavaScript Client

```javascript
class ApiClient {
    constructor(baseUrl, token = null) {
        this.baseUrl = baseUrl;
        this.token = token;
    }
    
    setToken(token) {
        this.token = token;
    }
    
    async request(method, endpoint, data = null) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        const config = {
            method,
            headers
        };
        
        if (data) {
            config.body = JSON.stringify(data);
        }
        
        const response = await fetch(`${this.baseUrl}${endpoint}`, config);
        return response.json();
    }
    
    async login(email, password) {
        const data = await this.request('POST', '/login', { email, password });
        this.setToken(data.token);
        return data;
    }
    
    async getPosts(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request('GET', `/posts?${query}`);
    }
    
    async createPost(postData) {
        return this.request('POST', '/posts', postData);
    }
    
    async updatePost(id, postData) {
        return this.request('PUT', `/posts/${id}`, postData);
    }
    
    async deletePost(id) {
        return this.request('DELETE', `/posts/${id}`);
    }
}

// Usage
const api = new ApiClient('http://localhost:8000/api/v1');

// Login
await api.login('user@example.com', 'password');

// Get posts
const posts = await api.getPosts({ page: 1, per_page: 10 });

// Create post
const post = await api.createPost({
    title: 'New Post',
    content: 'Content here',
    category_id: 1,
    status: 'published'
});
```

## Best Practices

### 1. Use Proper HTTP Status Codes

```php
// 200 - Success
return response()->json($data, 200);

// 201 - Created
return response()->json($data, 201);

// 204 - No Content
return response()->json(null, 204);

// 400 - Bad Request
return response()->json(['error' => 'Invalid input'], 400);

// 401 - Unauthorized
return response()->json(['error' => 'Unauthorized'], 401);

// 403 - Forbidden
return response()->json(['error' => 'Forbidden'], 403);

// 404 - Not Found
return response()->json(['error' => 'Not found'], 404);

// 422 - Validation Error
return response()->json(['errors' => $errors], 422);

// 500 - Server Error
return response()->json(['error' => 'Server error'], 500);
```

### 2. Use API Resources

```php
// Good ✅
return new PostResource($post);
return PostResource::collection($posts);

// Bad ❌
return response()->json($post);
```

### 3. Implement Rate Limiting

```php
Route::middleware('throttle:60,1')->group(function() {
    // API routes
});
```

### 4. Version Your API

```php
Route::prefix('v1')->group(function() {
    // Version 1 routes
});

Route::prefix('v2')->group(function() {
    // Version 2 routes
});
```

### 5. Document Your API

Use OpenAPI/Swagger specification and tools like Postman or Insomnia.

## Next Steps

- Add API key authentication
- Implement OAuth2
- Add WebSocket support
- Create SDK libraries
- Set up API monitoring

## Resources

- [Authentication](../advanced/security.md)
- [Validation](../metadata/validation.md)
- [Testing](../advanced/testing.md)
- [API Best Practices](../contributing/best-practices.md)
