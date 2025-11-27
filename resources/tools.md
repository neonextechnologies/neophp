# Development Tools

Essential tools for NeoPHP development.

## Table of Contents

- [IDEs & Editors](#ides--editors)
- [CLI Tools](#cli-tools)
- [Database Tools](#database-tools)
- [Testing Tools](#testing-tools)
- [Debugging Tools](#debugging-tools)
- [Performance Tools](#performance-tools)
- [DevOps Tools](#devops-tools)
- [Browser Tools](#browser-tools)

---

## IDEs & Editors

### PHPStorm

**Best for:** Professional PHP development

**Features:**

- Intelligent code completion
- Refactoring tools
- Built-in debugging
- Database tools
- Version control integration
- NeoPHP support via plugin

**Setup:**

1. Install PHPStorm: https://jetbrains.com/phpstorm
2. Install NeoPHP plugin
3. Configure interpreter
4. Import code style settings

**Recommended Plugins:**

- NeoPHP Support
- PHP Annotations
- PHP Toolbox
- Database Navigator
- .env files support

**Download:** https://jetbrains.com/phpstorm  
**License:** Paid (Free for students/open source)

### VS Code

**Best for:** Lightweight, extensible editor

**Features:**

- Fast and lightweight
- Extensive extensions
- Integrated terminal
- Git integration
- Remote development

**Essential Extensions:**

```
PHP Intelephense
PHP Debug
PHP DocBlocker
PHP Namespace Resolver
PHPUnit Test Explorer
```

**NeoPHP Extensions:**

```
NeoPHP Snippets
NeoPHP Artisan
NeoPHP Blade
```

**Recommended Settings:**

```json
{
    "php.suggest.basic": false,
    "php.validate.enable": true,
    "php.validate.executablePath": "/usr/bin/php",
    "intelephense.files.maxSize": 5000000,
    "editor.formatOnSave": true,
    "editor.rulers": [80, 120],
    "files.associations": {
        "*.neo": "php"
    }
}
```

**Download:** https://code.visualstudio.com  
**License:** Free

### Sublime Text

**Best for:** Fast editing, large files

**Features:**

- Extremely fast
- Multiple cursors
- Command palette
- Split editing
- Plugin ecosystem

**Essential Packages:**

- PHP Companion
- PHPUnit Completions
- DocBlockr
- SublimeLinter-php
- GitGutter

**Download:** https://sublimetext.com  
**License:** Paid evaluation available

### Vim/Neovim

**Best for:** Terminal-based development

**Plugins:**

```vim
Plug 'neoclide/coc.nvim'
Plug 'phpactor/phpactor'
Plug 'stephpy/vim-php-cs-fixer'
Plug 'phpunit/vim-phpunit'
```

**Download:** https://neovim.io  
**License:** Free

---

## CLI Tools

### NeoPHP CLI

Built-in command-line interface.

**Installation:**

```bash
composer require neo/framework
```

**Common Commands:**

```bash
# View all commands
php neo list

# Create files
php neo make:controller UserController
php neo make:model Post
php neo make:migration create_posts_table

# Database
php neo migrate
php neo db:seed
php neo db:fresh

# Development server
php neo serve

# Tinker (REPL)
php neo tinker

# Cache
php neo cache:clear
php neo config:cache

# Queue
php neo queue:work
php neo queue:listen
```

### Composer

PHP dependency manager.

**Installation:**

```bash
# macOS/Linux
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Windows
# Download from getcomposer.org
```

**Common Commands:**

```bash
# Install dependencies
composer install

# Add package
composer require vendor/package

# Update dependencies
composer update

# Remove package
composer remove vendor/package

# Dump autoload
composer dump-autoload

# Scripts
composer test
composer cs-fix
```

**Download:** https://getcomposer.org  
**License:** Free

### PHP CS Fixer

Code style fixer.

**Installation:**

```bash
composer require --dev friendsofphp/php-cs-fixer
```

**Usage:**

```bash
# Fix code style
./vendor/bin/php-cs-fixer fix

# Dry run
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Specific directory
./vendor/bin/php-cs-fixer fix app/
```

**Download:** https://github.com/FriendsOfPHP/PHP-CS-Fixer  
**License:** Free

### PHPStan

Static analysis tool.

**Installation:**

```bash
composer require --dev phpstan/phpstan
```

**Usage:**

```bash
# Analyze code
./vendor/bin/phpstan analyse

# Specific level (0-9)
./vendor/bin/phpstan analyse --level=8

# Generate baseline
./vendor/bin/phpstan analyse --generate-baseline
```

**Download:** https://phpstan.org  
**License:** Free

### Psalm

Static analysis tool.

**Installation:**

```bash
composer require --dev vimeo/psalm
```

**Usage:**

```bash
# Initialize
./vendor/bin/psalm --init

# Analyze
./vendor/bin/psalm

# Fix issues
./vendor/bin/psalm --alter --issues=all
```

**Download:** https://psalm.dev  
**License:** Free

---

## Database Tools

### TablePlus

Modern database GUI.

**Features:**

- Multiple database support
- Query editor
- Data editing
- Schema designer
- Fast performance

**Supported:**

- MySQL
- PostgreSQL
- SQLite
- MongoDB
- Redis

**Download:** https://tableplus.com  
**License:** Freemium

### DBeaver

Universal database tool.

**Features:**

- Cross-platform
- ER diagrams
- Query builder
- Data export/import
- Connection tunneling

**Download:** https://dbeaver.io  
**License:** Free (Community Edition)

### Sequel Pro (macOS)

MySQL database management.

**Features:**

- Simple interface
- Query editor
- Table editing
- Data export
- SSH tunneling

**Download:** https://sequelpro.com  
**License:** Free

### PHPMyAdmin

Web-based MySQL administration.

**Installation:**

```bash
composer require --dev phpmyadmin/phpmyadmin
```

**Access:** http://localhost/phpmyadmin

**Download:** https://phpmyadmin.net  
**License:** Free

### Redis Desktop Manager

Redis GUI client.

**Features:**

- Key management
- Console
- Server info
- Cluster support
- Slow log

**Download:** https://resp.app  
**License:** Freemium

---

## Testing Tools

### PHPUnit

Testing framework.

**Installation:**

```bash
composer require --dev phpunit/phpunit
```

**Usage:**

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/Unit/ExampleTest.php

# With coverage
./vendor/bin/phpunit --coverage-html coverage/

# Filter tests
./vendor/bin/phpunit --filter testMethodName
```

**Download:** https://phpunit.de  
**License:** Free

### Pest

Elegant testing framework.

**Installation:**

```bash
composer require --dev pestphp/pest
```

**Usage:**

```bash
# Run tests
./vendor/bin/pest

# With coverage
./vendor/bin/pest --coverage

# Parallel execution
./vendor/bin/pest --parallel
```

**Download:** https://pestphp.com  
**License:** Free

### Mockery

Mocking framework.

**Installation:**

```bash
composer require --dev mockery/mockery
```

**Usage:**

```php
$mock = Mockery::mock(UserRepository::class);
$mock->shouldReceive('find')
    ->once()
    ->with(123)
    ->andReturn($user);
```

**Download:** https://github.com/mockery/mockery  
**License:** Free

### Faker

Fake data generator.

**Installation:**

```bash
composer require --dev fakerphp/faker
```

**Usage:**

```php
$faker = Faker\Factory::create();

$name = $faker->name;
$email = $faker->email;
$address = $faker->address;
```

**Download:** https://fakerphp.github.io  
**License:** Free

---

## Debugging Tools

### Xdebug

PHP debugger and profiler.

**Installation:**

```bash
# Install via PECL
pecl install xdebug

# Enable in php.ini
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
```

**IDE Configuration:**

PHPStorm: Settings → PHP → Debug → Xdebug

VS Code: Install PHP Debug extension

**Features:**

- Step debugging
- Profiling
- Code coverage
- Stack traces

**Download:** https://xdebug.org  
**License:** Free

### NeoPHP Debugbar

Development debug bar.

**Installation:**

```bash
composer require --dev neo/debugbar
```

**Features:**

- SQL query monitoring
- Request/response inspection
- Timeline
- Memory usage
- Exception tracking

**Usage:**

Automatically enabled in development mode.

### NeoPHP Telescope

Application monitoring.

**Installation:**

```bash
composer require neo/telescope
```

**Features:**

- Request monitoring
- Database queries
- Queue jobs
- Mail sending
- Cache operations
- Redis commands
- Exception tracking

**Access:** http://localhost/telescope

**Download:** https://github.com/neophp/telescope  
**License:** Free

### Ray

Debug tool for PHP.

**Installation:**

```bash
composer require --dev spatie/ray
```

**Usage:**

```php
ray('Debug message');
ray($variable);
ray()->table(['Name' => 'John', 'Age' => 30]);
```

**Download:** https://myray.app  
**License:** Freemium

---

## Performance Tools

### Blackfire

PHP profiler.

**Installation:**

```bash
# Install probe
curl -sS https://packages.blackfire.io/gpg.key | sudo apt-key add -
sudo apt-get install blackfire-agent blackfire-php
```

**Features:**

- Profiling
- Performance recommendations
- Timeline analysis
- Comparisons

**Download:** https://blackfire.io  
**License:** Freemium

### Tideways

Performance monitoring.

**Features:**

- Real-time monitoring
- Request traces
- Error tracking
- Custom metrics

**Download:** https://tideways.com  
**License:** Paid

### New Relic

Application monitoring.

**Features:**

- Performance monitoring
- Error tracking
- Distributed tracing
- Infrastructure monitoring

**Download:** https://newrelic.com  
**License:** Freemium

### Apache Bench

Load testing tool.

**Installation:**

```bash
# Usually pre-installed
ab -V
```

**Usage:**

```bash
# 1000 requests, 10 concurrent
ab -n 1000 -c 10 http://localhost/

# With POST data
ab -n 100 -c 10 -p data.json -T application/json http://localhost/api/users
```

**License:** Free

### wrk

Modern HTTP benchmarking tool.

**Installation:**

```bash
# macOS
brew install wrk

# Linux
git clone https://github.com/wg/wrk.git
cd wrk
make
```

**Usage:**

```bash
# 12 threads, 400 connections for 30s
wrk -t12 -c400 -d30s http://localhost/
```

**Download:** https://github.com/wg/wrk  
**License:** Free

---

## DevOps Tools

### Docker

Containerization platform.

**Installation:**

Download from https://docker.com

**NeoPHP Docker Setup:**

```dockerfile
# Dockerfile
FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
```

```yaml
# docker-compose.yml
version: '3'
services:
  app:
    build: .
    volumes:
      - .:/var/www
  
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
  
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: neophp
  
  redis:
    image: redis:alpine
```

**Download:** https://docker.com  
**License:** Free

### GitHub Actions

CI/CD platform.

**Example Workflow:**

```yaml
# .github/workflows/tests.yml
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
        php-version: 8.1
    
    - name: Install Dependencies
      run: composer install
    
    - name: Run Tests
      run: ./vendor/bin/phpunit
```

**Download:** https://github.com/features/actions  
**License:** Free (with limits)

### GitLab CI

CI/CD platform.

**Example Pipeline:**

```yaml
# .gitlab-ci.yml
test:
  image: php:8.1
  script:
    - apt-get update -yqq
    - apt-get install -yqq git
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install
    - ./vendor/bin/phpunit
```

**Download:** https://gitlab.com  
**License:** Free

### Ansible

Configuration management.

**Installation:**

```bash
# macOS
brew install ansible

# Ubuntu
sudo apt install ansible
```

**Example Playbook:**

```yaml
# deploy.yml
---
- hosts: webservers
  tasks:
    - name: Pull latest code
      git:
        repo: https://github.com/user/repo.git
        dest: /var/www/app
    
    - name: Install dependencies
      composer:
        command: install
        working_dir: /var/www/app
```

**Download:** https://ansible.com  
**License:** Free

---

## Browser Tools

### Browser DevTools

Built-in browser developer tools.

**Chrome DevTools:**

- Open: F12 or Cmd+Option+I
- Features: Network, Console, Elements, Performance

**Firefox Developer Tools:**

- Open: F12 or Cmd+Option+I
- Features: Similar to Chrome

### Postman

API development tool.

**Features:**

- API testing
- Collections
- Environment variables
- Automated testing
- Documentation

**Download:** https://postman.com  
**License:** Freemium

### Insomnia

REST/GraphQL client.

**Features:**

- Clean interface
- Environment variables
- Code generation
- Plugin support

**Download:** https://insomnia.rest  
**License:** Free

### HTTPie

Command-line HTTP client.

**Installation:**

```bash
# macOS
brew install httpie

# pip
pip install httpie
```

**Usage:**

```bash
# GET request
http GET localhost/api/users

# POST request
http POST localhost/api/users name=John email=john@example.com

# With auth
http GET localhost/api/profile Authorization:"Bearer token"
```

**Download:** https://httpie.io  
**License:** Free

---

## Recommended Stack

### Minimal Setup

- **Editor:** VS Code
- **Database:** TablePlus
- **Testing:** PHPUnit
- **Debugging:** Xdebug

### Professional Setup

- **IDE:** PHPStorm
- **Database:** TablePlus/DBeaver
- **Testing:** Pest
- **Debugging:** NeoPHP Telescope + Xdebug
- **Profiling:** Blackfire
- **API Testing:** Postman
- **Version Control:** Git + GitHub/GitLab

### Enterprise Setup

Above plus:

- **Monitoring:** New Relic/Tideways
- **CI/CD:** GitHub Actions/GitLab CI
- **Containers:** Docker
- **Deployment:** Ansible
- **Load Testing:** wrk

---

## Tool Integration

### PHPStorm + NeoPHP

1. Install NeoPHP plugin
2. Configure PHP interpreter
3. Enable Composer
4. Configure Xdebug
5. Import code style

### VS Code + NeoPHP

1. Install extensions
2. Configure settings.json
3. Set up tasks
4. Configure launch.json for debugging

### Docker + NeoPHP

See [Docker Setup](#docker) section above.

---

## Next Steps

1. **Choose your IDE** - Start with VS Code or PHPStorm
2. **Set up debugging** - Install and configure Xdebug
3. **Install CLI tools** - Get Composer, PHP CS Fixer, PHPStan
4. **Database management** - Install TablePlus or DBeaver
5. **API testing** - Get Postman or Insomnia

**Happy coding!** 🛠️
