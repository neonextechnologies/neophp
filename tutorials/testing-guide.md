# Testing Guide

Comprehensive guide to testing NeoPhP applications.

## Testing Philosophy

Write tests that:
- Are fast and isolated
- Test behavior, not implementation
- Are easy to read and maintain
- Provide confidence in your code
- Document expected behavior

## Test Structure

### Organize Tests

```
tests/
├── Unit/                      # Isolated component tests
│   ├── Services/
│   ├── Models/
│   └── Helpers/
├── Feature/                   # Integration tests
│   ├── Auth/
│   ├── Api/
│   └── Http/
├── Integration/               # External service tests
│   ├── Database/
│   └── Cache/
└── E2E/                       # End-to-end tests
    └── Browser/
```

## Unit Testing

### Testing Services

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DiscountService;
use App\Models\{User, Product};

class DiscountServiceTest extends TestCase
{
    private DiscountService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiscountService();
    }
    
    public function test_calculates_percentage_discount(): void
    {
        $result = $this->service->calculateDiscount(100, 10, 'percentage');
        
        $this->assertEquals(10, $result);
    }
    
    public function test_calculates_fixed_discount(): void
    {
        $result = $this->service->calculateDiscount(100, 25, 'fixed');
        
        $this->assertEquals(25, $result);
    }
    
    public function test_discount_cannot_exceed_price(): void
    {
        $result = $this->service->calculateDiscount(100, 150, 'fixed');
        
        $this->assertEquals(100, $result);
    }
    
    public function test_applies_user_discount(): void
    {
        $user = User::factory()->create(['discount_rate' => 15]);
        $product = Product::factory()->create(['price' => 100]);
        
        $finalPrice = $this->service->applyUserDiscount($user, $product);
        
        $this->assertEquals(85, $finalPrice);
    }
}
```

### Testing Models

```php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Order;

class OrderTest extends TestCase
{
    public function test_order_number_is_generated(): void
    {
        $order = Order::factory()->create();
        
        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }
    
    public function test_calculates_total_correctly(): void
    {
        $order = Order::factory()
            ->hasItems(3, ['price' => 10, 'quantity' => 2])
            ->create();
        
        $this->assertEquals(60, $order->calculateTotal());
    }
    
    public function test_can_be_cancelled_when_pending(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $this->assertTrue($order->canBeCancelled());
    }
    
    public function test_cannot_be_cancelled_when_shipped(): void
    {
        $order = Order::factory()->create(['status' => 'shipped']);
        
        $this->assertFalse($order->canBeCancelled());
    }
}
```

### Testing Helpers

```php
<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class StringHelperTest extends TestCase
{
    public function test_truncates_string(): void
    {
        $result = str_truncate('Hello World', 5);
        
        $this->assertEquals('Hello...', $result);
    }
    
    public function test_generates_random_string(): void
    {
        $result = str_random(10);
        
        $this->assertEquals(10, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $result);
    }
    
    /**
     * @dataProvider slugProvider
     */
    public function test_generates_slug(string $input, string $expected): void
    {
        $result = str_slug($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function slugProvider(): array
    {
        return [
            ['Hello World', 'hello-world'],
            ['Hello  World', 'hello-world'],
            ['Hello-World', 'hello-world'],
            ['Héllo Wörld', 'hello-world'],
        ];
    }
}
```

## Feature Testing

### Testing HTTP Endpoints

```php
<?php

namespace Tests\Feature\Http;

use Tests\TestCase;
use App\Models\{User, Post};

class PostControllerTest extends TestCase
{
    public function test_guest_can_view_posts(): void
    {
        Post::factory()->count(3)->create(['status' => 'published']);
        
        $response = $this->get('/posts');
        
        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
        $response->assertViewHas('posts');
    }
    
    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'published'
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $user->id
        ]);
    }
    
    public function test_user_can_update_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($user)->put("/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);
        
        $response->assertRedirect();
        
        $this->assertEquals('Updated Title', $post->fresh()->title);
    }
    
    public function test_user_cannot_update_others_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(); // Different user
        
        $response = $this->actingAs($user)->put("/posts/{$post->id}", [
            'title' => 'Hacked Title'
        ]);
        
        $response->assertStatus(403);
    }
}
```

### Testing Authentication

```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use NeoPhp\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);
        
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
    
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password'
        ]);
        
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
    
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
```

### Testing API Endpoints

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
                '*' => ['id', 'title', 'content', 'created_at']
            ],
            'meta' => ['total', 'per_page', 'current_page']
        ]);
    }
    
    public function test_can_create_post_with_authentication(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/posts', [
                'title' => 'API Test Post',
                'content' => 'Content here',
                'status' => 'published'
            ]);
        
        $response->assertStatus(201);
        $response->assertJsonPath('data.title', 'API Test Post');
    }
    
    public function test_cannot_create_post_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/posts', [
            'title' => 'Test Post'
        ]);
        
        $response->assertStatus(401);
    }
    
    private function withToken(string $token): self
    {
        return $this->withHeader('Authorization', "Bearer {$token}");
    }
}
```

## Database Testing

### Using Factories

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Post, Comment};

class BlogTest extends TestCase
{
    public function test_user_can_comment_on_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'content' => 'Great post!'
        ]);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Great post!'
        ]);
    }
    
    public function test_post_with_comments_is_deleted_with_comments(): void
    {
        $post = Post::factory()
            ->has(Comment::factory()->count(3))
            ->create();
        
        $post->delete();
        
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertEquals(0, Comment::where('post_id', $post->id)->count());
    }
}
```

### Database Transactions

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use NeoPhp\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{
    use DatabaseTransactions;
    
    public function test_order_creation(): void
    {
        // Test data is automatically rolled back after each test
        $order = Order::create([/* ... */]);
        
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
```

### Seeding Test Data

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Database\Seeders\TestDataSeeder;

class ReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(TestDataSeeder::class);
    }
    
    public function test_generates_sales_report(): void
    {
        $response = $this->get('/reports/sales');
        
        $response->assertStatus(200);
        $response->assertViewHas('totalSales');
    }
}
```

## Mocking and Faking

### Mocking Services

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PaymentService;
use Mockery;

class CheckoutTest extends TestCase
{
    public function test_processes_payment_successfully(): void
    {
        $paymentService = Mockery::mock(PaymentService::class);
        $paymentService->shouldReceive('charge')
            ->once()
            ->with(100.00, Mockery::any())
            ->andReturn(['success' => true, 'transaction_id' => 'txn_123']);
        
        $this->app->instance(PaymentService::class, $paymentService);
        
        $response = $this->post('/checkout', [/* ... */]);
        
        $response->assertRedirect('/success');
    }
}
```

### Faking Facades

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use NeoPhp\Mail\Facades\Mail;
use NeoPhp\Storage\Facades\Storage;
use NeoPhp\Queue\Facades\Queue;
use App\Mail\WelcomeEmail;

class UserRegistrationTest extends TestCase
{
    public function test_sends_welcome_email_on_registration(): void
    {
        Mail::fake();
        
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        
        Mail::assertSent(WelcomeEmail::class, function($mail) {
            return $mail->hasTo('john@example.com');
        });
    }
    
    public function test_uploads_avatar(): void
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $response = $this->post('/profile/avatar', [
            'avatar' => $file
        ]);
        
        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }
    
    public function test_dispatches_welcome_job(): void
    {
        Queue::fake();
        
        $this->post('/register', [/* ... */]);
        
        Queue::assertPushed(SendWelcomeEmailJob::class);
    }
}
```

### Faking Time

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use NeoPhp\Support\Facades\Date;

class SubscriptionTest extends TestCase
{
    public function test_subscription_expires(): void
    {
        Date::freeze('2024-01-01 00:00:00');
        
        $subscription = Subscription::create([
            'expires_at' => now()->addDays(30)
        ]);
        
        $this->assertFalse($subscription->isExpired());
        
        Date::travel(31)->days();
        
        $this->assertTrue($subscription->isExpired());
    }
}
```

## Testing Best Practices

### 1. Arrange-Act-Assert Pattern

```php
public function test_creates_user(): void
{
    // Arrange
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ];
    
    // Act
    $user = User::create($data);
    
    // Assert
    $this->assertEquals('John Doe', $user->name);
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
}
```

### 2. Test Edge Cases

```php
public function test_handles_empty_cart(): void
{
    $cart = new ShoppingCart();
    
    $this->assertEquals(0, $cart->total());
    $this->assertTrue($cart->isEmpty());
}

public function test_handles_large_quantity(): void
{
    $product = Product::factory()->create(['stock' => 10]);
    
    $result = $product->canOrder(1000);
    
    $this->assertFalse($result);
}
```

### 3. Use Data Providers

```php
/**
 * @dataProvider emailProvider
 */
public function test_validates_email(string $email, bool $isValid): void
{
    $validator = new EmailValidator();
    
    $this->assertEquals($isValid, $validator->validate($email));
}

public function emailProvider(): array
{
    return [
        ['valid@example.com', true],
        ['invalid@', false],
        ['@example.com', false],
        ['valid+tag@example.com', true],
    ];
}
```

### 4. Test One Thing Per Test

```php
// Good ✅
public function test_creates_user(): void
{
    $user = User::create($this->userData);
    $this->assertInstanceOf(User::class, $user);
}

public function test_stores_user_in_database(): void
{
    User::create($this->userData);
    $this->assertDatabaseHas('users', ['email' => $this->userData['email']]);
}

// Bad ❌
public function test_user_creation(): void
{
    $user = User::create($this->userData);
    $this->assertInstanceOf(User::class, $user);
    $this->assertDatabaseHas('users', ['email' => $this->userData['email']]);
    $this->assertEquals('John', $user->name);
}
```

### 5. Use Descriptive Test Names

```php
// Good ✅
public function test_user_cannot_delete_post_they_do_not_own(): void
public function test_subscription_automatically_renews_before_expiry(): void
public function test_guest_is_redirected_to_login_page(): void

// Bad ❌
public function test_delete(): void
public function test_subscription(): void
public function test_redirect(): void
```

## Test Coverage

### Generate Coverage Report

```bash
php neo test --coverage
php neo test --coverage-html=coverage
```

### Target Coverage Goals

- **Overall**: 80%+ coverage
- **Critical paths**: 100% coverage
- **Business logic**: 90%+ coverage
- **Controllers**: 70%+ coverage

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo_mysql
          
      - name: Install dependencies
        run: composer install
        
      - name: Run tests
        run: php neo test
        
      - name: Upload coverage
        uses: codecov/codecov-action@v2
```

## Performance Testing

```php
<?php

namespace Tests\Performance;

use Tests\TestCase;

class QueryPerformanceTest extends TestCase
{
    public function test_post_list_query_is_fast(): void
    {
        Post::factory()->count(1000)->create();
        
        $start = microtime(true);
        
        Post::with('user', 'category')->paginate(20);
        
        $duration = microtime(true) - $start;
        
        $this->assertLessThan(0.1, $duration, 'Query took too long');
    }
}
```

## Next Steps

- Set up continuous integration
- Add code coverage monitoring
- Implement mutation testing
- Create E2E test suite
- Add performance benchmarks

## Resources

- [PHPUnit Documentation](https://phpunit.de)
- [Mockery Documentation](http://docs.mockery.io)
- [Testing Best Practices](../contributing/best-practices.md)
