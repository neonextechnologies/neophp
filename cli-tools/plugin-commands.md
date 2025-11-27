# Plugin Commands

NeoPhp's plugin system provides commands for managing, creating, and maintaining plugins.

## Plugin Management

### List Plugins

```bash
# List all plugins
php neo plugin:list

# Sample output:
# +---------------+---------+----------+-------------+
# | Plugin        | Status  | Version  | Author      |
# +---------------+---------+----------+-------------+
# | Shop          | Enabled | 1.2.0    | NeoNext     |
# | Blog          | Enabled | 2.0.1    | NeoNext     |
# | Analytics     | Disabled| 1.0.0    | ThirdParty  |
# +---------------+---------+----------+-------------+

# Show only enabled plugins
php neo plugin:list --enabled

# Show only disabled plugins
php neo plugin:list --disabled

# Show detailed information
php neo plugin:list --detailed
```

### Install Plugin

```bash
# Install plugin from directory
php neo plugin:install shop

# Install from zip file
php neo plugin:install shop.zip

# Install from URL
php neo plugin:install https://example.com/plugins/shop.zip

# Install and enable automatically
php neo plugin:install shop --activate

# Force reinstall
php neo plugin:install shop --force
```

### Enable Plugin

```bash
# Enable single plugin
php neo plugin:enable shop

# Enable multiple plugins
php neo plugin:enable shop blog analytics

# Enable all disabled plugins
php neo plugin:enable --all
```

### Disable Plugin

```bash
# Disable single plugin
php neo plugin:disable shop

# Disable multiple plugins
php neo plugin:disable shop blog

# Disable all plugins
php neo plugin:disable --all

# Force disable (skip cleanup)
php neo plugin:disable shop --force
```

### Uninstall Plugin

```bash
# Uninstall plugin
php neo plugin:uninstall shop

# Uninstall and remove data
php neo plugin:uninstall shop --clean

# Force uninstall without confirmation
php neo plugin:uninstall shop --force
```

### Update Plugin

```bash
# Update single plugin
php neo plugin:update shop

# Update all plugins
php neo plugin:update --all

# Check for updates only
php neo plugin:update --check-only
```

## Plugin Development

### Create New Plugin

```bash
# Create plugin with wizard
php neo make:plugin MyPlugin

# Create with namespace
php neo make:plugin MyCompany/MyPlugin

# Create with full structure
php neo make:plugin MyPlugin --full

# Sample prompts:
# Plugin name: MyPlugin
# Description: My awesome plugin
# Author: Your Name
# Version: 1.0.0
# License: MIT
# Creating plugin structure...
```

Generated structure:

```
plugins/
└── MyPlugin/
    ├── Plugin.php              # Main plugin class
    ├── config.php              # Plugin configuration
    ├── routes.php              # Plugin routes
    ├── composer.json           # Dependencies
    ├── README.md               # Documentation
    ├── src/
    │   ├── Controllers/        # Plugin controllers
    │   ├── Models/             # Plugin models
    │   ├── Services/           # Business logic
    │   └── Helpers/            # Helper functions
    ├── views/                  # Plugin views
    ├── assets/
    │   ├── css/               # Stylesheets
    │   ├── js/                # JavaScript
    │   └── images/            # Images
    ├── database/
    │   ├── migrations/        # Plugin migrations
    │   └── seeders/           # Plugin seeders
    └── tests/                 # Plugin tests
```

### Plugin.php Template

```php
<?php

namespace Plugins\MyPlugin;

use NeoPhp\Plugin\Plugin;
use NeoPhp\Plugin\HookManager;

class MyPlugin extends Plugin
{
    /**
     * Plugin information
     */
    public function getInfo(): array
    {
        return [
            'name' => 'MyPlugin',
            'description' => 'My awesome plugin',
            'version' => '1.0.0',
            'author' => 'Your Name',
            'license' => 'MIT',
        ];
    }
    
    /**
     * Plugin dependencies
     */
    public function getDependencies(): array
    {
        return [
            // 'OtherPlugin' => '>=1.0.0',
        ];
    }
    
    /**
     * Install plugin
     */
    public function install(): void
    {
        // Run migrations
        $this->runMigrations();
        
        // Create default data
        $this->createDefaults();
    }
    
    /**
     * Boot plugin
     */
    public function boot(): void
    {
        // Register routes
        $this->loadRoutes();
        
        // Register views
        $this->loadViews();
        
        // Add hooks
        $this->registerHooks();
    }
    
    /**
     * Uninstall plugin
     */
    public function uninstall(): void
    {
        // Remove plugin data
        $this->cleanupData();
    }
    
    /**
     * Register plugin hooks
     */
    private function registerHooks(): void
    {
        HookManager::addAction('app.booted', [$this, 'onAppBooted']);
        HookManager::addFilter('menu.items', [$this, 'addMenuItems']);
    }
}
```

### Create Plugin Controller

```bash
# Create controller for plugin
php neo plugin:make:controller ProductController --plugin=Shop

# Create resource controller
php neo plugin:make:controller ProductController --plugin=Shop --resource

# Create API controller
php neo plugin:make:controller ProductController --plugin=Shop --api
```

### Create Plugin Model

```bash
# Create model for plugin
php neo plugin:make:model Product --plugin=Shop

# Create with migration
php neo plugin:make:model Product --plugin=Shop --migration

# Create with all files
php neo plugin:make:model Product --plugin=Shop --all
```

### Create Plugin Migration

```bash
# Create migration for plugin
php neo plugin:make:migration create_products_table --plugin=Shop

# Create from model
php neo plugin:make:migration --from-model=Product --plugin=Shop
```

### Create Plugin View

```bash
# Create view for plugin
php neo plugin:make:view products.index --plugin=Shop

# Create layout
php neo plugin:make:view layouts.main --plugin=Shop --layout
```

## Plugin Testing

### Run Plugin Tests

```bash
# Run tests for specific plugin
php neo plugin:test Shop

# Run with coverage
php neo plugin:test Shop --coverage

# Run specific test class
php neo plugin:test Shop --class=ProductTest

# Run all plugin tests
php neo plugin:test --all
```

### Validate Plugin

```bash
# Validate plugin structure
php neo plugin:validate Shop

# Sample output:
# ✓ Plugin class exists
# ✓ Required methods implemented
# ✓ Dependencies satisfied
# ✓ Configuration valid
# ✓ Routes loaded successfully
# ⚠ Missing README.md
# ✗ Tests directory not found
```

## Plugin Information

### Show Plugin Info

```bash
# Show detailed plugin information
php neo plugin:info Shop

# Sample output:
# Name: Shop
# Description: E-commerce plugin for NeoPhp
# Version: 1.2.0
# Author: NeoNext Technologies
# License: MIT
# Status: Enabled
# Dependencies: Payment >= 1.0.0
# Installed: 2024-01-15 10:30:00
# Updated: 2024-01-20 14:45:00
```

### Show Plugin Routes

```bash
# List plugin routes
php neo plugin:routes Shop

# Sample output:
# +--------+----------------------------+---------------------------+
# | Method | URI                        | Action                    |
# +--------+----------------------------+---------------------------+
# | GET    | /shop                      | ShopController@index      |
# | GET    | /shop/products             | ProductController@index   |
# | POST   | /shop/products             | ProductController@store   |
# | GET    | /shop/products/{id}        | ProductController@show    |
# +--------+----------------------------+---------------------------+
```

### Show Plugin Hooks

```bash
# List plugin hooks
php neo plugin:hooks Shop

# Sample output:
# Actions:
# - product.created
# - product.updated
# - order.placed
# 
# Filters:
# - product.price
# - cart.total
# - shipping.cost
```

## Plugin Assets

### Publish Assets

```bash
# Publish plugin assets to public directory
php neo plugin:publish Shop

# Publish specific asset type
php neo plugin:publish Shop --type=css
php neo plugin:publish Shop --type=js
php neo plugin:publish Shop --type=images

# Force republish
php neo plugin:publish Shop --force
```

### Link Assets

```bash
# Create symbolic links to plugin assets
php neo plugin:link Shop

# Link all plugins
php neo plugin:link --all
```

## Plugin Configuration

### Publish Config

```bash
# Publish plugin config to app config
php neo plugin:config:publish Shop

# Edit plugin config
php neo plugin:config:edit Shop
```

### Show Config

```bash
# Show plugin configuration
php neo plugin:config Shop

# Sample output:
# enabled: true
# settings:
#   currency: USD
#   tax_rate: 0.08
#   shipping:
#     flat_rate: 10.00
#     free_shipping_threshold: 100.00
```

## Plugin Marketplace

### Search Plugins

```bash
# Search marketplace
php neo plugin:search ecommerce

# Sample output:
# Found 3 plugins:
# 
# 1. Shop Pro
#    E-commerce platform with advanced features
#    Version: 2.0.0 | Downloads: 5.2k | Rating: 4.8/5
# 
# 2. Simple Shop
#    Lightweight e-commerce plugin
#    Version: 1.5.0 | Downloads: 2.1k | Rating: 4.5/5
```

### Install from Marketplace

```bash
# Install plugin from marketplace
php neo plugin:install shop-pro --marketplace

# Install specific version
php neo plugin:install shop-pro:2.0.0 --marketplace
```

## Plugin Migration

### Run Plugin Migrations

```bash
# Run migrations for specific plugin
php neo plugin:migrate Shop

# Rollback plugin migrations
php neo plugin:migrate:rollback Shop

# Refresh plugin migrations
php neo plugin:migrate:refresh Shop

# Run migrations for all plugins
php neo plugin:migrate --all
```

### Seed Plugin Data

```bash
# Seed plugin data
php neo plugin:seed Shop

# Seed specific seeder
php neo plugin:seed Shop --class=ProductSeeder
```

## Plugin Export/Import

### Export Plugin

```bash
# Export plugin as zip
php neo plugin:export Shop

# Export with data
php neo plugin:export Shop --with-data

# Export to specific location
php neo plugin:export Shop --output=/path/to/shop.zip
```

### Import Plugin

```bash
# Import plugin from zip
php neo plugin:import shop.zip

# Import and activate
php neo plugin:import shop.zip --activate
```

## Plugin Optimization

### Cache Plugin Data

```bash
# Cache plugin configurations
php neo plugin:cache

# Clear plugin cache
php neo plugin:cache:clear
```

### Optimize Autoloading

```bash
# Optimize plugin autoloading
php neo plugin:optimize

# Clear optimization
php neo plugin:optimize:clear
```

## Troubleshooting

### Plugin Not Loading

```bash
# Debug plugin
php neo plugin:debug Shop

# Sample output:
# Plugin: Shop
# Status: Enabled
# Class: Plugins\Shop\Shop
# File exists: ✓
# Class exists: ✓
# Dependencies satisfied: ✓
# Boot method: ✓
# Error: None
```

### Check Dependencies

```bash
# Check plugin dependencies
php neo plugin:dependencies Shop

# Sample output:
# Dependencies for Shop:
# ✓ Payment (1.0.0) - Installed: 1.2.0
# ✗ Shipping (2.0.0) - Not installed
# ⚠ Tax (1.5.0) - Installed: 1.4.0 (needs update)
```

### Repair Plugin

```bash
# Repair plugin installation
php neo plugin:repair Shop

# Reinstall plugin
php neo plugin:reinstall Shop
```

## Best Practices

### 1. Always Validate Before Installing

```bash
# Good ✅
php neo plugin:validate shop.zip
php neo plugin:install shop.zip

# Check dependencies
php neo plugin:dependencies Shop
```

### 2. Backup Before Updates

```bash
# Backup plugin data
php neo db:backup --tables=shop_products,shop_orders

# Then update
php neo plugin:update Shop
```

### 3. Test in Development First

```bash
# Test plugin
php neo plugin:test Shop

# Validate
php neo plugin:validate Shop

# Then enable in production
```

### 4. Use Version Constraints

```php
// In plugin dependencies
public function getDependencies(): array
{
    return [
        'Payment' => '>=1.0.0,<2.0.0',
        'Shipping' => '^2.0',
    ];
}
```

### 5. Clean Uninstall

```bash
# Proper uninstallation
php neo plugin:uninstall Shop --clean

# This removes:
# - Plugin files
# - Database tables
# - Configuration
# - Assets
```

## Next Steps

- [Plugin Development Guide](../plugins/development.md)
- [Plugin Hooks](../plugins/hooks.md)
- [Plugin Examples](../plugins/examples.md)
- [Creating Plugins](make:plugin.md)
