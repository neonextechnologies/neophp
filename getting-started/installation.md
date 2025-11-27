# Installation

## Requirements

Before installing NeoPhp, make sure your server meets the following requirements:

* PHP 8.0 or higher
* Composer
* MySQL 5.7+ / PostgreSQL 9.6+ / SQLite 3.8+ (optional)
* Git

## Installation via Composer

Create a new NeoPhp project using Composer:

```bash
composer create-project neonextechnologies/neophp my-project
cd my-project
```

## Installation via Git

Clone the repository directly:

```bash
git clone https://github.com/neonextechnologies/neophp.git my-project
cd my-project
composer install
```

## Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
# Application
APP_NAME=NeoPhp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neophp
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=file

# Queue
QUEUE_CONNECTION=sync
```

## Database Setup

Run migrations to create database tables:

```bash
php neo migrate
```

## Development Server

Start the built-in development server:

```bash
php neo serve
```

Visit http://localhost:8000 in your browser.

## Verify Installation

Check that everything is working:

```bash
php neo --version
php neo list
```

You should see the NeoPhp CLI version and available commands.

## Next Steps

* [Quick Start Guide](quick-start.md)
* [Configuration](configuration.md)
* [Directory Structure](directory-structure.md)

## Troubleshooting

### Composer Not Found

Install Composer from [getcomposer.org](https://getcomposer.org)

### Permission Errors

Make sure storage directories are writable:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### PHP Version

Check your PHP version:

```bash
php -v
```

NeoPhp requires PHP 8.0 or higher.
