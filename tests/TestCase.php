<?php

namespace Vermaysha\PgbouncerLaravelExtension\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PDO;
use Vermaysha\PgbouncerLaravelExtension\PgbouncerExtensionServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Load migrations from the tests/database/migrations directory
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            PgbouncerExtensionServiceProvider::class,
        ];
    }


    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup a default database connection using environment variables
        // defined in phpunit.xml.dist
        $app['config']->set('database.default', 'pgsql_test');
        $app['config']->set('database.connections.pgsql_test', [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'testing'),
            'username' => env('DB_USERNAME', 'sail'),
            'password' => env('DB_PASSWORD', 'password'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
            'options' => [
                // PDO::ATTR_EMULATE_PREPARES => true,
            ],
        ]);
    }
}
