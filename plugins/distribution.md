# Plugin Distribution

Package and distribute your NeoPhp plugins.

## Packaging

### 1. Prepare Plugin

Ensure your plugin is complete:

- [ ] All features implemented
- [ ] Tests passing
- [ ] Documentation complete
- [ ] README.md included
- [ ] LICENSE file included
- [ ] Version number updated
- [ ] Dependencies listed

### 2. Clean Directory

Remove development files:

```bash
# Remove .git directory
rm -rf .git

# Remove node_modules
rm -rf node_modules

# Remove vendor (will be installed by users)
rm -rf vendor

# Remove development files
rm -rf tests
rm .gitignore
rm .env.example
```

### 3. Create Package

#### ZIP Archive

```bash
cd plugins
zip -r my-plugin-1.0.0.zip my-plugin/
```

#### TAR Archive

```bash
cd plugins
tar -czf my-plugin-1.0.0.tar.gz my-plugin/
```

### 4. Verify Package

Extract and test:

```bash
# Extract
unzip my-plugin-1.0.0.zip -d test-install

# Test installation
cd test-install
php neo plugin:activate my-plugin
```

## Distribution Channels

### 1. NeoPhp Marketplace

Official plugin marketplace.

#### Submit Plugin

```bash
# Login to marketplace
php neo marketplace:login

# Publish plugin
php neo marketplace:publish plugins/my-plugin
```

#### Update Plugin

```bash
# Update version in plugin.json
# Then publish update
php neo marketplace:update my-plugin
```

### 2. GitHub Releases

Host on GitHub:

```bash
# Create release
git tag v1.0.0
git push origin v1.0.0

# Upload ZIP as release asset
```

Install from GitHub:

```bash
php neo plugin:install github:username/my-plugin
```

### 3. Private Repository

Host on private server:

```bash
# Upload to server
scp my-plugin-1.0.0.zip user@server:/plugins/

# Install from URL
php neo plugin:install https://example.com/plugins/my-plugin-1.0.0.zip
```

## Versioning

### Semantic Versioning

Format: MAJOR.MINOR.PATCH

```json
{
    "version": "1.2.3"
}
```

- **MAJOR**: Breaking changes (1.x.x → 2.0.0)
- **MINOR**: New features, backward compatible (1.1.x → 1.2.0)
- **PATCH**: Bug fixes (1.2.1 → 1.2.2)

### Version Constraints

In plugin.json:

```json
{
    "requires": {
        "neophp": "^1.0",      // 1.0.0 to 1.x.x
        "php": "^8.1",          // 8.1.0 to 8.x.x
        "other-plugin": "~2.3"  // 2.3.0 to 2.4.0
    }
}
```

### Changelog

Maintain CHANGELOG.md:

```markdown
# Changelog

## [1.2.0] - 2024-01-15

### Added
- New analytics dashboard
- Export to CSV feature

### Changed
- Improved performance of reports
- Updated UI design

### Fixed
- Fixed date range selector bug
- Corrected calculation error

## [1.1.0] - 2023-12-01

### Added
- Real-time tracking
- Custom events

### Fixed
- Fixed timezone issues
```

## Documentation

### README.md

Complete plugin documentation:

```markdown
# My Plugin

Brief description of what the plugin does.

## Features

- Feature 1
- Feature 2
- Feature 3

## Requirements

- NeoPhp ^1.0
- PHP ^8.1
- MySQL 5.7+

## Installation

\`\`\`bash
php neo plugin:install my-plugin
php neo plugin:activate my-plugin
\`\`\`

## Configuration

1. Copy configuration file:
   \`\`\`bash
   cp plugins/my-plugin/config/example.php config/my-plugin.php
   \`\`\`

2. Update settings in `config/my-plugin.php`

3. Add to `.env`:
   \`\`\`
   MY_PLUGIN_API_KEY=your-key
   MY_PLUGIN_ENABLED=true
   \`\`\`

## Usage

### Basic Usage

\`\`\`php
use MyPlugin\Services\MyService;

$service = app(MyService::class);
$result = $service->doSomething();
\`\`\`

### Advanced Usage

\`\`\`php
// Advanced example
\`\`\`

## Hooks

### Actions

- `my_plugin.event` - Fired when event occurs
- `my_plugin.before_action` - Before action executes

### Filters

- `my_plugin.data` - Filter data before processing
- `my_plugin.output` - Filter output before rendering

## CLI Commands

\`\`\`bash
# Command 1
php neo my-plugin:command

# Command 2
php neo my-plugin:another --option=value
\`\`\`

## API

Refer to [API documentation](docs/api.md).

## Testing

\`\`\`bash
php neo test plugins/my-plugin/tests
\`\`\`

## Contributing

1. Fork repository
2. Create feature branch
3. Make changes
4. Submit pull request

## Support

- Documentation: https://docs.example.com
- Issues: https://github.com/username/my-plugin/issues
- Email: support@example.com

## License

MIT License - see LICENSE file

## Credits

Created by [Your Name](https://example.com)
```

### API Documentation

Document all public APIs:

```markdown
# API Reference

## Classes

### MyService

Main service class.

#### Methods

##### doSomething()

Performs action.

**Parameters:**
- `$param1` (string) - Description
- `$param2` (int) - Description

**Returns:** `array` - Result data

**Example:**
\`\`\`php
$service = app(MyService::class);
$result = $service->doSomething('value', 123);
\`\`\`

**Throws:**
- `InvalidArgumentException` - If parameters invalid
- `RuntimeException` - If operation fails
```

## License

### Choose License

Common options:

- **MIT**: Permissive, allows commercial use
- **GPL**: Copyleft, requires derivatives to be GPL
- **Apache 2.0**: Permissive with patent grant
- **Proprietary**: Commercial, restricted use

### LICENSE File

```
MIT License

Copyright (c) 2024 Your Name

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## Marketing

### Plugin Page

Create compelling plugin page:

- **Title**: Clear, descriptive name
- **Description**: Brief overview (1-2 sentences)
- **Features**: Bullet points of key features
- **Screenshots**: 3-5 high-quality images
- **Demo**: Live demo link if possible
- **Documentation**: Link to docs
- **Support**: Contact information

### Screenshots

Capture key features:

1. Main interface
2. Configuration screen
3. Key feature in action
4. Results/output
5. Mobile responsive view

### Demo Video

Create walkthrough video:

1. Installation (30 seconds)
2. Configuration (1 minute)
3. Key features (2 minutes)
4. Advanced usage (1 minute)
5. Call to action (30 seconds)

## Pricing

### Free Plugins

- Basic features
- Community support
- Open source

### Premium Plugins

```json
{
    "pricing": {
        "personal": {
            "price": 29,
            "period": "year",
            "sites": 1,
            "support": "email"
        },
        "professional": {
            "price": 99,
            "period": "year",
            "sites": 5,
            "support": "priority"
        },
        "business": {
            "price": 299,
            "period": "year",
            "sites": "unlimited",
            "support": "24/7"
        }
    }
}
```

### Freemium Model

- Free version: Basic features
- Pro version: Advanced features
- Upsell in plugin

## Updates

### Automatic Updates

Enable auto-updates:

```php
public function checkForUpdates(): ?array
{
    $response = Http::get('https://api.example.com/plugins/my-plugin/version');
    
    $latest = $response->json('version');
    $current = $this->getVersion();
    
    if (version_compare($latest, $current, '>')) {
        return [
            'version' => $latest,
            'download_url' => $response->json('download_url'),
            'changelog' => $response->json('changelog')
        ];
    }
    
    return null;
}
```

### Update Notifications

Notify users of updates:

```php
public function boot(): void
{
    if ($update = $this->checkForUpdates()) {
        $this->addAction('admin.notices', function() use ($update) {
            echo "New version {$update['version']} available!";
        });
    }
}
```

## Analytics

Track plugin usage:

```php
public function trackInstall(): void
{
    Http::post('https://api.example.com/analytics', [
        'plugin' => $this->name,
        'version' => $this->getVersion(),
        'site_url' => url('/'),
        'php_version' => PHP_VERSION,
        'neophp_version' => app()->version()
    ]);
}
```

## Support

### Support Channels

- **Documentation**: Comprehensive docs
- **FAQ**: Common questions
- **Forum**: Community discussion
- **Email**: Direct support
- **Tickets**: Issue tracking
- **Chat**: Real-time help (premium)

### Response Times

- Free: 48-72 hours
- Personal: 24 hours
- Professional: 12 hours
- Business: 2 hours

## Best Practices

### 1. Semantic Versioning

Always use proper versioning.

### 2. Comprehensive Documentation

Document everything clearly.

### 3. Automated Testing

Test before releasing.

### 4. Security Scanning

Check for vulnerabilities.

### 5. License Compliance

Ensure all dependencies licensed correctly.

### 6. Regular Updates

Maintain and update regularly.

### 7. User Feedback

Listen and respond to users.

## Next Steps

- [Plugin Development](development.md)
- [Plugin API](plugin-api.md)
- [Testing](../testing/introduction.md)
- [Security](../security/best-practices.md)
