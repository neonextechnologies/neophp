# Testing

Write comprehensive tests to ensure application quality.

## Configuration

Configure testing in `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

## Unit Testing

### Basic Unit Test

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Calculator;

class CalculatorTest extends TestCase
{
    private Calculator $calculator;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new Calculator();
    }
    
    public function test_can_add_numbers(): void
    {
        $result = $this->calculator->add(2, 3);
        
        $this->assertEquals(5, $result);
    }
    
    public function test_can_subtract_numbers(): void
    {
        $result = $this->calculator->subtract(10, 4);
        
        $this->assertEquals(6, $result);
    }
    
    public function test_division_by_zero_throws_exception(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        
        $this->calculator->divide(10, 0);
    }
}
```

### Testing Services

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Models\User;

class UserServiceTest extends TestCase
{
    private UserService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }
    
    public function test_can_create_user(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $user = $this->service->create($data);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }
    
    public function test_can_update_user(): void
    {
        $user = User::factory()->create();
        
        $updated = $this->service->update($user, [
            'name' => 'Jane Doe'
        ]);
        
        $this->assertEquals('Jane Doe', $updated->name);
    }
    
    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();
        
        $this->service->delete($user);
        
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
```

## Feature Testing

### HTTP Testing

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    public function test_can_view_users_list(): void
    {
        $users = User::factory()->count(3)->create();
        
        $response = $this->get('/users');
        
        $response->assertStatus(200);
        $response->assertSee($users[0]->name);
    }
    
    public function test_can_create_user(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $response = $this->post('/users', $data);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }
    
    public function test_validation_fails_for_invalid_email(): void
    {
        $response = $this->post('/users', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
    
    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Name'
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $user->fresh()->name);
    }
}
```

### JSON API Testing

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{User, Post};

class PostApiTest extends TestCase
{
    public function test_can_get_posts_list(): void
    {
        Post::factory()->count(5)->create();
        
        $response = $this->getJson('/api/posts');
        
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'created_at']
            ]
        ]);
    }
    
    public function test_can_create_post(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Test content'
        ]);
        
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'title' => 'Test Post',
                'content' => 'Test content'
            ]
        ]);
    }
    
    public function test_can_update_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($user)->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title'
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('Updated Title', $post->fresh()->title);
    }
    
    public function test_cannot_update_other_users_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $response = $this->actingAs($user)->putJson("/api/posts/{$post->id}", [
            'title' => 'Hacked Title'
        ]);
        
        $response->assertStatus(403);
    }
}
```

## Database Testing

### Database Factories

```php
<?php

namespace Database\Factories;

use App\Models\User;
use NeoPhp\Database\Factory;

class UserFactory extends Factory
{
    protected string $model = User::class;
    
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
    }
    
    public function unverified(): self
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }
    
    public function admin(): self
    {
        return $this->state([
            'role' => 'admin',
        ]);
    }
}

// Usage
$user = User::factory()->create();
$users = User::factory()->count(10)->create();
$admin = User::factory()->admin()->create();
$unverified = User::factory()->unverified()->create();
```

### Database Assertions

```php
public function test_user_creation(): void
{
    $user = User::factory()->create([
        'email' => 'test@example.com'
    ]);
    
    // Assert exists in database
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com'
    ]);
    
    // Assert count
    $this->assertDatabaseCount('users', 1);
}

public function test_user_deletion(): void
{
    $user = User::factory()->create();
    
    $user->delete();
    
    // Assert soft deleted
    $this->assertSoftDeleted('users', [
        'id' => $user->id
    ]);
    
    // Assert missing
    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
        'deleted_at' => null
    ]);
}
```

## Mocking

### Mocking Dependencies

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\{UserService, MailService};
use App\Models\User;
use Mockery;

class UserServiceTest extends TestCase
{
    public function test_sends_welcome_email_on_registration(): void
    {
        $mailService = Mockery::mock(MailService::class);
        $mailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->with(Mockery::type(User::class));
        
        $this->app->instance(MailService::class, $mailService);
        
        $userService = new UserService($mailService);
        $user = $userService->register([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
    }
}
```

### Faking Facades

```php
use NeoPhp\Mail\Facades\Mail;
use NeoPhp\Storage\Facades\Storage;
use NeoPhp\Queue\Facades\Queue;

public function test_sends_order_confirmation(): void
{
    Mail::fake();
    
    $order = Order::factory()->create();
    $this->service->sendOrderConfirmation($order);
    
    Mail::assertSent(OrderConfirmation::class, function($mail) use ($order) {
        return $mail->order->id === $order->id;
    });
}

public function test_uploads_file(): void
{
    Storage::fake('public');
    
    $file = UploadedFile::fake()->image('photo.jpg');
    $this->service->uploadPhoto($file);
    
    Storage::disk('public')->assertExists('photos/' . $file->hashName());
}

public function test_dispatches_job(): void
{
    Queue::fake();
    
    $this->service->processOrder($order);
    
    Queue::assertPushed(ProcessOrderJob::class, function($job) use ($order) {
        return $job->orderId === $order->id;
    });
}
```

## Complete Examples

### Testing User Registration

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use NeoPhp\Mail\Facades\Mail;
use App\Mail\WelcomeEmail;

class RegistrationTest extends TestCase
{
    public function test_user_can_register(): void
    {
        Mail::fake();
        
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        Mail::assertSent(WelcomeEmail::class);
    }
    
    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);
        
        $response->assertSessionHasErrors(['email']);
    }
    
    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);
        
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        
        $response->assertSessionHasErrors(['email']);
    }
    
    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different'
        ]);
        
        $response->assertSessionHasErrors(['password']);
    }
}
```

### Testing Order Processing

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Product, Order};
use NeoPhp\Queue\Facades\Queue;
use App\Jobs\{ProcessPaymentJob, SendOrderConfirmationJob};

class OrderProcessingTest extends TestCase
{
    private User $user;
    private Product $product;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['price' => 100]);
    }
    
    public function test_can_create_order(): void
    {
        Queue::fake();
        
        $response = $this->actingAs($this->user)->post('/orders', [
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total' => 200
        ]);
        
        Queue::assertPushed(ProcessPaymentJob::class);
        Queue::assertPushed(SendOrderConfirmationJob::class);
    }
    
    public function test_cannot_order_out_of_stock_product(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        
        $response = $this->actingAs($this->user)->post('/orders', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }
    
    public function test_order_reduces_product_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        
        $this->actingAs($this->user)->post('/orders', [
            'product_id' => $product->id,
            'quantity' => 3
        ]);
        
        $this->assertEquals(7, $product->fresh()->stock);
    }
}
```

### Testing API Authentication

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{User, ApiKey};

class ApiAuthenticationTest extends TestCase
{
    public function test_can_access_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token');
        
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');
        
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email
            ]
        ]);
    }
    
    public function test_cannot_access_without_token(): void
    {
        $response = $this->getJson('/api/user');
        
        $response->assertStatus(401);
    }
    
    public function test_cannot_access_with_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/user');
        
        $response->assertStatus(401);
    }
}
```

### Testing File Uploads

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use NeoPhp\Http\UploadedFile;
use NeoPhp\Storage\Facades\Storage;
use App\Models\User;

class FileUploadTest extends TestCase
{
    public function test_can_upload_avatar(): void
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file
        ]);
        
        $response->assertStatus(200);
        
        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'avatar' => 'avatars/' . $file->hashName()
        ]);
    }
    
    public function test_validates_file_type(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf');
        
        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['avatar']);
    }
    
    public function test_validates_file_size(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg')->size(10000); // 10MB
        
        $response = $this->actingAs($user)->post('/profile/avatar', [
            'avatar' => $file
        ]);
        
        $response->assertStatus(422);
    }
}
```

## Testing Middleware

```php
<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;

class AdminMiddlewareTest extends TestCase
{
    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();
        
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
    }
    
    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertStatus(403);
    }
    
    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        
        $response->assertRedirect('/login');
    }
}
```

## Performance Testing

```php
<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\User;

class UserQueryPerformanceTest extends TestCase
{
    public function test_user_list_query_performance(): void
    {
        User::factory()->count(1000)->create();
        
        $start = microtime(true);
        
        $users = User::with('posts', 'profile')->paginate(20);
        
        $duration = microtime(true) - $start;
        
        $this->assertLessThan(0.5, $duration, 'Query took too long');
    }
}
```

## Browser Testing (Dusk)

```php
<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;

class LoginTest extends DuskTestCase
{
    public function test_user_can_login(): void
    {
        $user = User::factory()->create();
        
        $this->browse(function($browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/dashboard')
                ->assertSee($user->name);
        });
    }
}
```

## Best Practices

### 1. Test One Thing Per Test

```php
// Good ✅
public function test_can_create_user(): void
{
    $user = $this->service->create($data);
    $this->assertInstanceOf(User::class, $user);
}

public function test_stores_user_in_database(): void
{
    $this->service->create($data);
    $this->assertDatabaseHas('users', ['email' => $data['email']]);
}

// Bad ❌
public function test_user_creation(): void
{
    $user = $this->service->create($data);
    $this->assertInstanceOf(User::class, $user);
    $this->assertDatabaseHas('users', ['email' => $data['email']]);
    $this->assertTrue($user->email_verified);
}
```

### 2. Use Descriptive Test Names

```php
// Good ✅
test_user_cannot_update_other_users_post()
test_registration_requires_valid_email()
test_admin_can_delete_any_post()

// Bad ❌
test_post_update()
test_validation()
test_admin()
```

### 3. Use Factories

```php
// Good ✅
$user = User::factory()->create();
$posts = Post::factory()->count(5)->create();

// Bad ❌
$user = new User(['name' => 'Test', 'email' => 'test@test.com']);
```

### 4. Clean Up After Tests

```php
protected function tearDown(): void
{
    Storage::fake('public')->deleteDirectory('uploads');
    parent::tearDown();
}
```

### 5. Use Database Transactions

```php
use NeoPhp\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;
    
    // Tests automatically rolled back
}
```

## Running Tests

```bash
# Run all tests
php neo test

# Run specific test suite
php neo test --testsuite=Unit

# Run specific test file
php neo test tests/Unit/UserServiceTest.php

# Run with coverage
php neo test --coverage

# Run in parallel
php neo test --parallel
```

## Next Steps

- [Caching](caching.md)
- [Queue System](queue.md)
- [Security](security.md)
- [Contributing Guidelines](../contributing/guidelines.md)
