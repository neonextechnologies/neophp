# Contributing Guidelines

Thank you for considering contributing to NeoPHP! This guide will help you understand our development process and how to contribute effectively.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Process](#development-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)
- [Pull Request Process](#pull-request-process)
- [Testing Requirements](#testing-requirements)
- [Documentation](#documentation)

---

## Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity, level of experience, nationality, personal appearance, race, religion, or sexual identity.

### Our Standards

**Positive behavior includes:**

- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards others

**Unacceptable behavior includes:**

- Trolling, insulting comments, and personal attacks
- Public or private harassment
- Publishing others' private information
- Other conduct which could be considered inappropriate

### Enforcement

Instances of abusive behavior may be reported to the project team. All complaints will be reviewed and investigated promptly and fairly.

---

## Getting Started

### Prerequisites

Before contributing, ensure you have:

- PHP 8.0 or higher
- Composer
- Git
- MySQL/PostgreSQL (for database features)
- Redis (optional, for cache/queue features)

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:

```bash
git clone https://github.com/YOUR-USERNAME/neophp.git
cd neophp
```

3. Add upstream remote:

```bash
git remote add upstream https://github.com/neonextechnologies/neophp.git
```

### Install Dependencies

```bash
composer install
```

### Setup Development Environment

1. Copy environment file:

```bash
cp .env.example .env
```

2. Configure your database and other services in `.env`

3. Run tests to verify setup:

```bash
composer test
```

---

## Development Process

### Branching Strategy

We use Git Flow branching model:

- `main` - Production-ready code
- `develop` - Integration branch for features
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Urgent production fixes
- `release/*` - Release preparation

### Creating a Feature Branch

```bash
# Update develop branch
git checkout develop
git pull upstream develop

# Create feature branch
git checkout -b feature/your-feature-name
```

### Workflow

1. **Create branch** from `develop`
2. **Make changes** with clear, atomic commits
3. **Write tests** for your changes
4. **Update documentation** as needed
5. **Run tests** and ensure they pass
6. **Push to your fork**
7. **Create Pull Request** to `develop`

### Commit Messages

We follow [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**

```bash
feat(database): add support for PostgreSQL connection pooling

Add connection pooling feature for PostgreSQL to improve performance
under high load. Includes configuration options and automatic
connection management.

Closes #123
```

```bash
fix(validation): resolve email validation regex issue

Fix regex pattern that was incorrectly rejecting valid email
addresses with plus signs.

Fixes #456
```

```bash
docs(readme): update installation instructions

Add detailed steps for Windows installation and troubleshooting
common issues.
```

---

## Reporting Bugs

### Before Submitting

1. **Search existing issues** to avoid duplicates
2. **Test with latest version** to ensure bug still exists
3. **Collect relevant information**:
   - NeoPHP version
   - PHP version
   - Operating system
   - Database type and version
   - Steps to reproduce
   - Expected vs actual behavior
   - Error messages/stack traces

### Bug Report Template

```markdown
**NeoPHP Version:** 1.0.0
**PHP Version:** 8.1.0
**OS:** Ubuntu 22.04
**Database:** MySQL 8.0

**Description:**
Brief description of the bug.

**Steps to Reproduce:**
1. Step one
2. Step two
3. Step three

**Expected Behavior:**
What should happen.

**Actual Behavior:**
What actually happens.

**Error Messages:**
```
Paste error messages or stack traces here
```

**Additional Context:**
Any other relevant information.
```

### Example Bug Report

**Title:** Query Builder fails with nested where clauses

```markdown
**NeoPHP Version:** 1.0.0
**PHP Version:** 8.1.2
**OS:** macOS 12.4
**Database:** MySQL 8.0.29

**Description:**
The Query Builder throws a SQL syntax error when using nested where
clauses with orWhere conditions.

**Steps to Reproduce:**
1. Create a query with nested where clauses:
```php
DB::table('users')
    ->where(function($query) {
        $query->where('status', 'active')
              ->orWhere('status', 'pending');
    })
    ->where('role', 'admin')
    ->get();
```

2. Execute the query

**Expected Behavior:**
Query should execute successfully and return matching records.

**Actual Behavior:**
SQL syntax error is thrown.

**Error Messages:**
```
SQLSTATE[42000]: Syntax error or access violation: 1064 You have an
error in your SQL syntax near 'OR status = ?'
```

**Additional Context:**
The issue only occurs with orWhere inside a closure. Regular where
clauses work fine.
```

---

## Suggesting Features

### Before Submitting

1. **Search existing feature requests**
2. **Discuss in Discussions tab** for major features
3. **Consider if it fits NeoPHP's scope**

### Feature Request Template

```markdown
**Feature Description:**
Clear description of the proposed feature.

**Problem it Solves:**
What problem does this feature address?

**Proposed Solution:**
How should this feature work?

**Alternatives Considered:**
Any alternative solutions you've considered.

**Additional Context:**
Any other relevant information, examples, or mockups.
```

### Example Feature Request

**Title:** Add Redis Cluster support for caching

```markdown
**Feature Description:**
Add support for Redis Cluster mode in the caching system.

**Problem it Solves:**
Currently, NeoPHP only supports single Redis instances. For high-
availability production environments, Redis Cluster support is essential
for scalability and fault tolerance.

**Proposed Solution:**
Extend the Redis cache driver to support cluster configuration:

```php
'redis' => [
    'driver' => 'redis',
    'cluster' => true,
    'options' => [
        'cluster' => 'redis',
    ],
    'clusters' => [
        'default' => [
            ['host' => '127.0.0.1', 'port' => 6379],
            ['host' => '127.0.0.1', 'port' => 6380],
            ['host' => '127.0.0.1', 'port' => 6381],
        ],
    ],
]
```

**Alternatives Considered:**
- Using multiple single-instance drivers with manual failover
- External load balancer (adds infrastructure complexity)

**Additional Context:**
Laravel and Symfony both support Redis Cluster. Implementation could
reference their approaches while maintaining NeoPHP conventions.
```

---

## Pull Request Process

### Before Creating PR

1. **Ensure tests pass**:
   ```bash
   composer test
   ```

2. **Run code style checks**:
   ```bash
   composer cs-check
   ```

3. **Fix code style issues**:
   ```bash
   composer cs-fix
   ```

4. **Update documentation** if needed

5. **Rebase on latest develop**:
   ```bash
   git fetch upstream
   git rebase upstream/develop
   ```

### Creating the PR

1. **Push your branch**:
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create Pull Request** on GitHub to `develop` branch

3. **Fill out PR template** completely

### PR Template

```markdown
**Description:**
Brief description of changes.

**Type of Change:**
- [ ] Bug fix (non-breaking change fixing an issue)
- [ ] New feature (non-breaking change adding functionality)
- [ ] Breaking change (fix or feature causing existing functionality to change)
- [ ] Documentation update

**Related Issue:**
Closes #(issue number)

**Changes Made:**
- Change 1
- Change 2
- Change 3

**Testing:**
Describe testing performed.

**Checklist:**
- [ ] Code follows style guidelines
- [ ] Self-review performed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] Tests added/updated
- [ ] All tests passing
- [ ] No new warnings generated
```

### Review Process

1. **Automated checks** must pass (CI/CD)
2. **Code review** by maintainers
3. **Address feedback** and update PR
4. **Approval** from at least one maintainer
5. **Merge** to develop branch

### After Merge

1. **Delete your feature branch**:
   ```bash
   git branch -d feature/your-feature-name
   git push origin --delete feature/your-feature-name
   ```

2. **Update your fork**:
   ```bash
   git checkout develop
   git pull upstream develop
   git push origin develop
   ```

---

## Testing Requirements

### Test Coverage

- All new features must include tests
- Bug fixes should include regression tests
- Aim for 80%+ code coverage on new code

### Types of Tests

**Unit Tests:**

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Neo\Support\Str;

class StrTest extends TestCase
{
    public function test_slug_converts_string_to_slug()
    {
        $result = Str::slug('Hello World');
        
        $this->assertEquals('hello-world', $result);
    }
    
    public function test_slug_handles_special_characters()
    {
        $result = Str::slug('Hello & World!');
        
        $this->assertEquals('hello-and-world', $result);
    }
}
```

**Feature Tests:**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
./vendor/bin/phpunit tests/Unit/StrTest.php

# Run with coverage
composer test-coverage

# Run specific test method
./vendor/bin/phpunit --filter test_slug_converts_string_to_slug
```

---

## Documentation

### When to Update Docs

- New features
- API changes
- Configuration changes
- Breaking changes
- Examples and tutorials

### Documentation Structure

```
docs/
├── getting-started/
├── core-concepts/
├── cli-tools/
├── database/
├── advanced/
├── tutorials/
├── api-reference/
└── contributing/
```

### Writing Documentation

**Clear and Concise:**

```markdown
## Creating Migrations

Generate a new migration file:

```bash
php neo make:migration create_users_table
```

This creates a migration file in `database/migrations/` with a timestamp prefix.
```

**Code Examples:**

```markdown
## Using Query Builder

Basic query example:

```php
$users = DB::table('users')
    ->where('active', true)
    ->orderBy('name')
    ->get();
```

With joins:

```php
$posts = DB::table('posts')
    ->join('users', 'users.id', '=', 'posts.user_id')
    ->select('posts.*', 'users.name')
    ->get();
```
```

**API Reference:**

```markdown
### `where($column, $operator, $value)`

Add a WHERE clause to the query.

**Parameters:**
- `$column` (string) - Column name
- `$operator` (string) - Comparison operator
- `$value` (mixed) - Value to compare

**Returns:** `QueryBuilder`

**Example:**
```php
$users = DB::table('users')
    ->where('age', '>', 18)
    ->get();
```
```

---

## Community

### Getting Help

- **Documentation:** https://docs.neophp.dev
- **GitHub Discussions:** For questions and discussions
- **GitHub Issues:** For bug reports and feature requests
- **Discord:** [Join our Discord](https://discord.gg/neophp)

### Staying Updated

- Watch the repository for updates
- Follow [@NeoPHP](https://twitter.com/neophp) on Twitter
- Subscribe to our newsletter

---

## Recognition

Contributors are recognized in:

- `CONTRIBUTORS.md` file
- Release notes for significant contributions
- Annual contributor spotlight

---

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

## Questions?

If you have questions about contributing, please:

1. Check existing documentation
2. Search GitHub Discussions
3. Ask in Discord
4. Open a Discussion on GitHub

Thank you for contributing to NeoPHP! 🚀
