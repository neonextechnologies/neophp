# Code Style Guide

Consistent code style makes the codebase easier to read and maintain. This guide outlines the coding standards for NeoPHP.

## Table of Contents

- [PSR Standards](#psr-standards)
- [PHP Standards](#php-standards)
- [Naming Conventions](#naming-conventions)
- [Code Structure](#code-structure)
- [Documentation](#documentation)
- [Best Practices](#best-practices)
- [Tools](#tools)

---

## PSR Standards

NeoPHP follows these PHP Standard Recommendations:

- **PSR-1:** Basic Coding Standard
- **PSR-2:** Coding Style Guide (deprecated but base for PSR-12)
- **PSR-4:** Autoloading Standard
- **PSR-11:** Container Interface
- **PSR-12:** Extended Coding Style Guide
- **PSR-16:** Simple Cache Interface

---

## PHP Standards

### File Format

**PHP Tags:**

```php
<?php

// Code here
```

**File Encoding:**

- UTF-8 without BOM
- Unix line endings (LF)

**Closing Tags:**

Never use closing PHP tags `?>` in files containing only PHP code.

### Indentation

- Use 4 spaces for indentation
- Never use tabs

```php
<?php

namespace Neo\Database;

class Connection
{
    protected $pdo;
    
    public function __construct($config)
    {
        $this->pdo = new PDO(
            $config['dsn'],
            $config['username'],
            $config['password']
        );
    }
}
```

### Line Length

- Soft limit: 80 characters
- Hard limit: 120 characters
- Break long lines thoughtfully

### Blank Lines

```php
<?php

namespace Neo\Http;

use Neo\Support\Arr;

class Request
{
    protected $data = [];
    
    public function __construct()
    {
        // Constructor
    }
    
    public function input($key)
    {
        return Arr::get($this->data, $key);
    }
}
```

---

## Naming Conventions

### Classes

**PascalCase** for class names:

```php
class UserController {}
class OrderService {}
class PaymentGateway {}
```

### Methods

**camelCase** for method names:

```php
public function getUserById($id) {}
public function processPayment() {}
public function sendEmailNotification() {}
```

### Properties

**camelCase** for properties:

```php
protected $userName;
private $totalAmount;
public $isActive;
```

### Constants

**SCREAMING_SNAKE_CASE** for constants:

```php
const MAX_ITEMS = 100;
const DEFAULT_TIMEOUT = 30;
const API_VERSION = '1.0';
```

### Namespaces

**PascalCase** matching directory structure:

```php
namespace Neo\Database\Query;
namespace App\Http\Controllers;
namespace App\Services\Payment;
```

### Files

- Match class name: `UserController.php`
- Migrations: `YYYY_MM_DD_HHMMSS_create_users_table.php`
- Tests: `UserControllerTest.php`

---

## Code Structure

### Class Structure

Order class elements:

1. Traits
2. Constants
3. Properties (public → protected → private)
4. Constructor
5. Public methods
6. Protected methods
7. Private methods

```php
<?php

namespace App\Services;

use Neo\Support\Traits\Makeable;

class UserService
{
    use Makeable;
    
    const MAX_ATTEMPTS = 3;
    
    public $debug = false;
    
    protected $repository;
    
    private $cache;
    
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }
    
    public function findById($id)
    {
        return $this->repository->find($id);
    }
    
    protected function validateUser($user)
    {
        // Validation logic
    }
    
    private function logAccess($userId)
    {
        // Logging logic
    }
}
```

### Control Structures

**if/elseif/else:**

```php
if ($condition) {
    // Code
} elseif ($otherCondition) {
    // Code
} else {
    // Code
}
```

**switch:**

```php
switch ($value) {
    case 'option1':
        // Code
        break;
        
    case 'option2':
        // Code
        break;
        
    default:
        // Code
}
```

**for:**

```php
for ($i = 0; $i < $count; $i++) {
    // Code
}
```

**foreach:**

```php
foreach ($items as $item) {
    // Code
}

foreach ($items as $key => $value) {
    // Code
}
```

**while:**

```php
while ($condition) {
    // Code
}
```

**do-while:**

```php
do {
    // Code
} while ($condition);
```

### Try-Catch

```php
try {
    $this->processPayment();
} catch (PaymentException $e) {
    $this->handlePaymentError($e);
} catch (\Exception $e) {
    Log::error('Unexpected error', ['error' => $e->getMessage()]);
} finally {
    $this->cleanup();
}
```

### Method Declarations

```php
public function methodName(
    $param1,
    $param2,
    $param3 = null
) {
    // Code
}

// Short params on one line
public function shortMethod($param1, $param2)
{
    // Code
}

// Return type declarations
public function calculate(): int
{
    return 42;
}

// Nullable return types
public function findUser($id): ?User
{
    return User::find($id);
}
```

### Closures

```php
$closure = function ($param1, $param2) use ($var1, $var2) {
    // Code
};

// Multi-line
$closure = function (
    $longParameter1,
    $longParameter2
) use (
    $longVariable1,
    $longVariable2
) {
    // Code
};
```

---

## Documentation

### DocBlocks

**Class Documentation:**

```php
/**
 * User service for handling user operations
 *
 * This service provides methods for user management including
 * registration, authentication, and profile updates.
 *
 * @package App\Services
 * @author Your Name <your.email@example.com>
 */
class UserService
{
    // ...
}
```

**Method Documentation:**

```php
/**
 * Find user by ID
 *
 * Retrieves a user from the database by their unique identifier.
 * Returns null if user is not found.
 *
 * @param int $id User ID
 * @return User|null User instance or null
 * @throws UserNotFoundException If strict mode enabled and user not found
 */
public function findById(int $id): ?User
{
    return User::find($id);
}
```

**Property Documentation:**

```php
/**
 * User repository instance
 *
 * @var UserRepository
 */
protected $repository;

/**
 * Maximum login attempts allowed
 *
 * @var int
 */
const MAX_ATTEMPTS = 3;
```

**Complex Parameters:**

```php
/**
 * Create new order
 *
 * @param array $data Order data
 *     - items: array Array of order items
 *     - user_id: int User ID
 *     - shipping_address: array Shipping address details
 *     - payment_method: string Payment method (credit_card|paypal)
 * @return Order Created order instance
 */
public function createOrder(array $data): Order
{
    // ...
}
```

### Inline Comments

```php
// Calculate total with tax
$total = $subtotal + ($subtotal * $taxRate);

// Send confirmation email after successful registration
if ($user->save()) {
    Mail::to($user)->send(new WelcomeEmail($user));
}
```

**Explain Why, Not What:**

```php
// Good - explains reasoning
// Use cache to avoid expensive database queries during high traffic
$users = Cache::remember('active_users', 600, function() {
    return User::where('active', true)->get();
});

// Bad - states the obvious
// Get users from cache
$users = Cache::get('users');
```

---

## Best Practices

### Type Declarations

Use type declarations when possible:

```php
// Good
public function calculateTotal(int $quantity, float $price): float
{
    return $quantity * $price;
}

// Avoid
public function calculateTotal($quantity, $price)
{
    return $quantity * $price;
}
```

### Null Coalescing

```php
// Good
$name = $request->input('name') ?? 'Guest';

// Avoid
$name = isset($request->input('name')) ? $request->input('name') : 'Guest';
```

### Spaceship Operator

```php
// Good
usort($array, fn($a, $b) => $a <=> $b);

// Avoid
usort($array, function($a, $b) {
    if ($a == $b) return 0;
    return ($a < $b) ? -1 : 1;
});
```

### Arrow Functions

```php
// Good - for simple operations
$squared = array_map(fn($n) => $n * $n, $numbers);

// Use regular closures for complex logic
$results = array_map(function($item) {
    if ($item->valid()) {
        return $item->process();
    }
    return null;
}, $items);
```

### String Concatenation

```php
// Good - for simple cases
$message = "Hello, " . $name;

// Good - for complex strings
$message = sprintf(
    "Order #%s for %s (Total: $%.2f)",
    $order->id,
    $user->name,
    $order->total
);

// Good - with variables
$message = "Hello, {$user->name}. Your order total is ${$order->total}.";
```

### Array Syntax

Always use short array syntax:

```php
// Good
$array = ['item1', 'item2', 'item3'];
$associative = ['key' => 'value'];

// Avoid
$array = array('item1', 'item2', 'item3');
$associative = array('key' => 'value');
```

### Comparison

```php
// Use strict comparison
if ($value === true) {}
if ($count === 0) {}

// Use loose comparison only when needed
if ($value == '123') {}
```

### Early Returns

```php
// Good
public function process($data)
{
    if (!$this->validate($data)) {
        return false;
    }
    
    if (!$this->hasPermission()) {
        return false;
    }
    
    return $this->execute($data);
}

// Avoid deep nesting
public function process($data)
{
    if ($this->validate($data)) {
        if ($this->hasPermission()) {
            return $this->execute($data);
        }
    }
    
    return false;
}
```

### Single Responsibility

```php
// Good - focused responsibility
class UserValidator
{
    public function validate(array $data): bool
    {
        return $this->validateEmail($data['email'])
            && $this->validatePassword($data['password']);
    }
}

class UserCreator
{
    public function create(array $data): User
    {
        return User::create($data);
    }
}

// Avoid - too many responsibilities
class UserManager
{
    public function validate() {}
    public function create() {}
    public function update() {}
    public function delete() {}
    public function sendEmail() {}
    public function generateReport() {}
}
```

### Dependency Injection

```php
// Good
class OrderController
{
    protected $orderService;
    
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    public function store(Request $request)
    {
        return $this->orderService->create($request->validated());
    }
}

// Avoid
class OrderController
{
    public function store(Request $request)
    {
        $orderService = new OrderService();
        return $orderService->create($request->validated());
    }
}
```

---

## Tools

### PHP CS Fixer

Install PHP CS Fixer:

```bash
composer require --dev friendsofphp/php-cs-fixer
```

Configuration (`.php-cs-fixer.php`):

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('storage')
    ->exclude('bootstrap/cache');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
    ])
    ->setFinder($finder);
```

Run fixer:

```bash
# Check style
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix issues
./vendor/bin/php-cs-fixer fix
```

### PHPStan

Install PHPStan:

```bash
composer require --dev phpstan/phpstan
```

Configuration (`phpstan.neon`):

```neon
parameters:
    level: 8
    paths:
        - src
        - app
    excludePaths:
        - vendor
        - storage
        - bootstrap/cache
```

Run analysis:

```bash
./vendor/bin/phpstan analyse
```

### EditorConfig

`.editorconfig`:

```ini
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
indent_style = space
indent_size = 4
trim_trailing_whitespace = true

[*.md]
trim_trailing_whitespace = false

[*.{yml,yaml}]
indent_size = 2
```

### Composer Scripts

Add to `composer.json`:

```json
{
    "scripts": {
        "cs-check": "php-cs-fixer fix --dry-run --diff",
        "cs-fix": "php-cs-fixer fix",
        "phpstan": "phpstan analyse",
        "test": "phpunit",
        "check": [
            "@cs-check",
            "@phpstan",
            "@test"
        ]
    }
}
```

Usage:

```bash
composer cs-check
composer cs-fix
composer phpstan
composer check
```

---

## IDE Configuration

### PHPStorm

1. **Enable PSR-12:**
   - Settings → Editor → Code Style → PHP
   - Set from: PSR-12

2. **Enable inspections:**
   - Settings → Editor → Inspections → PHP
   - Enable relevant inspections

3. **Configure file watchers** for automatic formatting

### VS Code

Install extensions:

- PHP Intelephense
- PHP CS Fixer
- PHPStan

Settings (`.vscode/settings.json`):

```json
{
    "php.validate.executablePath": "/usr/bin/php",
    "php-cs-fixer.executablePath": "${workspaceFolder}/vendor/bin/php-cs-fixer",
    "php-cs-fixer.onsave": true,
    "editor.formatOnSave": true,
    "editor.rulers": [80, 120]
}
```

---

## Code Review Checklist

Before submitting code, verify:

- [ ] Follows PSR-12 coding standards
- [ ] Proper type declarations used
- [ ] DocBlocks present and accurate
- [ ] No unused imports
- [ ] Consistent naming conventions
- [ ] Early returns for readability
- [ ] Single responsibility principle
- [ ] Dependency injection used
- [ ] Tests included
- [ ] PHP CS Fixer passes
- [ ] PHPStan analysis passes

---

## Resources

- [PSR-12 Specification](https://www.php-fig.org/psr/psr-12/)
- [PHP The Right Way](https://phptherightway.com/)
- [Clean Code PHP](https://github.com/jupeter/clean-code-php)

---

## Questions?

If you have questions about code style:

1. Check this guide
2. Look at existing code for examples
3. Ask in GitHub Discussions
4. Request clarification in your PR

Consistent code style benefits everyone! 🎨
