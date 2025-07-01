<?php

namespace Vermaysha\PgbouncerLaravelExtension;

use DateTimeInterface;
use Illuminate\Database\PostgresConnection;
use PDO;

/**
 * Custom Postgres Connection class to handle value bindings correctly
 * when using PgBouncer with emulated prepared statements.
 */
class PostgresPGBouncerExtension extends PostgresConnection
{
    /**
     * Bind values to the PDOStatement.
     *
     * We override the original bindValues method from the parent class to handle
     * booleans correctly. Since we cast booleans to strings in prepareBindings,
     * we need to use PDO::PARAM_STR to bind them.
     *
     * @param  \PDOStatement  $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_resource($value) => PDO::PARAM_LOB,
                    $value === null => PDO::PARAM_NULL,
                    // Booleans are handled in prepareBindings, so they will be strings here
                    default => PDO::PARAM_STR
                },
            );
        }
    }

    /**
     * Prepare the bindings for the query.
     *
     * This method will cast booleans to strings ('true'/'false') which is necessary
     * for PgBouncer in certain modes.
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $bindings[$key] = json_encode($value);
            } elseif (is_object($value) && method_exists($value, '__toString')) {
                $bindings[$key] = (string) $value;
            }
        }

        return $bindings;
    }
}
