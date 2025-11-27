# CLI Tools

NeoPhp provides a powerful command-line interface (CLI) for automating common development tasks. The CLI tool, called `neo`, helps you generate code, manage databases, create plugins, and more.

## What is Neo CLI?

Neo CLI is a command-line tool that:
- Generates models, controllers, and migrations
- Creates complete CRUD operations automatically
- Manages database migrations and seeders
- Handles plugin installation and management
- Provides custom command creation

## Installation

Neo CLI is included with NeoPhp. After installing NeoPhp via Composer, the `neo` command is available:

```bash
# Check if neo is installed
php neo --version

# or
./neo --version
```

## Basic Usage

```bash
# Get help
php neo help

# List all available commands
php neo list

# Get help for a specific command
php neo help make:model
```

## Command Structure

```bash
php neo [command] [arguments] [options]
```

### Examples

```bash
# Command only
php neo migrate

# Command with argument
php neo make:model User

# Command with options
php neo make:model User --migration

# Command with multiple options
php neo make:model User --migration --controller --form
```

## Available Commands

### Code Generators

Generate boilerplate code automatically:

```bash
# Generate a model
php neo make:model Product

# Generate a controller
php neo make:controller ProductController

# Generate a migration
php neo make:migration create_products_table

# Generate everything at once
php neo make:model Product --all
```

### Database Commands

Manage your database:

```bash
# Run migrations
php neo migrate

# Rollback last migration
php neo migrate:rollback

# Reset database
php neo migrate:reset

# Refresh database
php neo migrate:refresh

# Run seeders
php neo db:seed
```

### Plugin Commands

Manage plugins:

```bash
# List installed plugins
php neo plugin:list

# Install a plugin
php neo plugin:install shop

# Enable a plugin
php neo plugin:enable shop

# Disable a plugin
php neo plugin:disable shop

# Uninstall a plugin
php neo plugin:uninstall shop
```

## Quick Start Examples

### Create a Complete CRUD Module

```bash
# Generate model with everything
php neo make:model Product --all

# This creates:
# - app/Models/Product.php (model)
# - app/Controllers/ProductController.php (controller)
# - migrations/xxx_create_products_table.php (migration)
# - views/products/*.php (views)
```

### Build a Blog System

```bash
# Create Post model
php neo make:model Post --all

# Create Category model
php neo make:model Category --all

# Create Comment model
php neo make:model Comment --migration

# Run migrations
php neo migrate

# Seed sample data
php neo db:seed --class=BlogSeeder
```

### Set Up E-commerce

```bash
# Products
php neo make:model Product --all

# Orders
php neo make:model Order --all

# Customers
php neo make:model Customer --all

# Install shop plugin
php neo plugin:install shop

# Run migrations
php neo migrate
```

## Common Workflows

### Starting a New Feature

```bash
# 1. Create model with migration and controller
php neo make:model Invoice --migration --controller

# 2. Edit the model and add metadata
nano app/Models/Invoice.php

# 3. Run migration
php neo migrate

# 4. Generate form
php neo make:form Invoice

# 5. Start development server
php -S localhost:8000 -t public
```

### Database Reset During Development

```bash
# Reset and re-run all migrations
php neo migrate:refresh

# Reset and seed data
php neo migrate:refresh --seed
```

### Plugin Development

```bash
# Create a new plugin
php neo make:plugin MyPlugin

# This creates:
# - plugins/MyPlugin/
#   - Plugin.php
#   - config.php
#   - routes.php
#   - etc.

# Enable for testing
php neo plugin:enable MyPlugin
```

## Command Options

### Common Options

Most commands support these options:

```bash
# Force overwrite existing files
php neo make:model User --force

# Run without prompts
php neo migrate --no-interaction

# Show detailed output
php neo migrate --verbose

# Quiet mode (no output)
php neo migrate --quiet
```

### Generator Options

```bash
# Generate with migration
php neo make:model User --migration
# or
php neo make:model User -m

# Generate with controller
php neo make:model User --controller
# or
php neo make:model User -c

# Generate with form
php neo make:model User --form
# or
php neo make:model User -f

# Generate everything
php neo make:model User --all
# or
php neo make:model User -a
```

## Configuration

Neo CLI can be configured in `config/neo.php`:

```php
<?php

return [
    // Command namespace
    'namespace' => 'App\\Commands',
    
    // Custom command paths
    'paths' => [
        'commands' => app_path('Commands'),
    ],
    
    // Generator settings
    'generators' => [
        'model' => [
            'path' => app_path('Models'),
            'namespace' => 'App\\Models',
        ],
        'controller' => [
            'path' => app_path('Controllers'),
            'namespace' => 'App\\Controllers',
        ],
        'migration' => [
            'path' => database_path('migrations'),
        ],
    ],
];
```

## Environment-Specific Commands

```bash
# Use specific environment
php neo migrate --env=production

# Or set environment variable
export NEO_ENV=production
php neo migrate
```

## Batch Operations

```bash
# Run multiple migrations
php neo migrate --path=database/migrations/2024

# Seed multiple classes
php neo db:seed --class=UsersSeeder,PostsSeeder,CommentsSeeder
```

## Troubleshooting

### Command Not Found

```bash
# Make neo executable (Linux/Mac)
chmod +x neo

# Use full PHP path
/usr/bin/php neo migrate

# Check PATH
echo $PATH
```

### Permission Issues

```bash
# Fix permissions (Linux/Mac)
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Windows - run as administrator
```

### Database Connection Errors

```bash
# Check database configuration
cat .env | grep DB_

# Test connection
php neo db:test-connection
```

## Next Steps

Learn about specific command groups:

- [Model Generator](generators/model.md)
- [Controller Generator](generators/controller.md)
- [Migration Generator](generators/migration.md)
- [Form Generator](generators/form.md)
- [CRUD Generator](generators/crud.md)
- [Database Commands](database-commands.md)
- [Plugin Commands](plugin-commands.md)
- [Custom Commands](custom-commands.md)

## Tips and Tricks

### Create Aliases

```bash
# Linux/Mac - add to ~/.bashrc or ~/.zshrc
alias neo="php $(pwd)/neo"

# Now you can just type:
neo make:model User
```

### Use Tab Completion

```bash
# Generate completion script
php neo completion bash > neo-completion.sh
source neo-completion.sh

# Now use tab completion
neo make:<TAB>
```

### Chain Commands

```bash
# Create model and immediately migrate
php neo make:model Product --migration && php neo migrate

# Refresh database and seed
php neo migrate:refresh && php neo db:seed
```

### Save Time with Shortcuts

```bash
# Instead of this:
php neo make:model User --migration --controller --form

# Use this:
php neo make:model User -mcf

# Or even better:
php neo make:model User --all
```

## Command Reference Quick Sheet

| Command | Description |
|---------|-------------|
| `make:model` | Generate a model |
| `make:controller` | Generate a controller |
| `make:migration` | Generate a migration |
| `make:form` | Generate a form |
| `make:crud` | Generate complete CRUD |
| `make:plugin` | Generate a plugin |
| `make:command` | Generate a custom command |
| `migrate` | Run migrations |
| `migrate:rollback` | Rollback migrations |
| `migrate:refresh` | Refresh database |
| `db:seed` | Run database seeders |
| `plugin:list` | List plugins |
| `plugin:install` | Install a plugin |
| `plugin:enable` | Enable a plugin |
| `plugin:disable` | Disable a plugin |

Get detailed help for any command:
```bash
php neo help [command]
```
