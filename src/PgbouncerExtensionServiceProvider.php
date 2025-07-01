<?php

namespace Vermaysha\PgbouncerLaravelExtension;

use Illuminate\Database\Connection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\ServiceProvider;
use PDO;

class PgbouncerExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            $emulatePrepare = $config['options'][PDO::ATTR_EMULATE_PREPARES] ?? false;

            if ($emulatePrepare) {
                return new PostgresPGBouncerExtension($connection, $database, $prefix, $config);
            }

            return new PostgresConnection($connection, $database, $prefix, $config);
        });
    }
}
