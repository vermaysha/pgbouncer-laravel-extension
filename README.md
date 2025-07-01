# Laravel PgBouncer Emulated Prepare Extension
A simple Laravel extension to enable the use of PgBouncer in transaction pooling mode by correctly handling value bindings when `PDO::ATTR_EMULATE_PREPARES` is enabled for a PostgreSQL connection.

## The Problem
When using PgBouncer in transaction pooling mode (`pool_mode = transaction`), it does not support prepared statements. The standard solution for Laravel is to disable them by setting `PDO::ATTR_EMULATE_PREPARES` to `true` in your `config/database.php` file.

However, when this option is enabled, Laravel's default `PostgresConnection` does not correctly handle boolean values, casting them to `1` or `0` instead of the required `'true'` or `'false'` strings, which leads to SQL errors.

This package transparently solves this issue by providing a custom connection class that correctly handles data type conversions for emulated prepared statements.

## Installation
You can install the package via Composer:

```bash
composer require vermaysha/pgbouncer-laravel-extension
```

The package will automatically register its service provider.

## Usage
After installation, the only step is to configure your PostgreSQL database connection to use emulated prepared statements.

In your `config/database.php` file, find your `pgsql` connection and add the `options` key with `PDO::ATTR_EMULATE_PREPARES` set to `true`.

```php
// config/database.php

'connections' => [

    'pgsql' => [
        'driver' => 'pgsql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'laravel'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
        
        // Add this option
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true,
        ],
    ],
],
```

That's it! The service provider will detect this setting and automatically use the custom connection class for any `pgsql` connection that has emulated prepares enabled.

## Testing
This package is fully tested. To run the tests locally, first set up your environment:

Ensure you have Docker installed and running.

Start the test database using Docker Compose:

```bash
docker-compose up -d
```

Run the test suite using Composer:

```bash
composer test
```

Alternatively, you can run phpunit directly:

```bash
vendor/bin/phpunit
```

## Contributing
Contributions are welcome! Please feel free to submit a pull request or create an issue for any bugs or feature requests.

## Credits
Author: [Ashary Vermaysha](https://github.com/vermaysha)

Contributor: [Google Gemini](https://gemini.google.com)

## License
The MIT License (MIT). Please see the [License File](LICENSE) for more information.