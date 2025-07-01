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
     * Bind values to their parameters in the given statement.
     *
     * This override ensures that boolean values are correctly cast to strings ('true'/'false')
     * before being bound, which is necessary for PgBouncer in certain modes.
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
     * Prepare the query bindings for execution.
     *
     * This override converts boolean values to their string representation ('true' or 'false')
     * because PgBouncer with emulated prepares might not handle native booleans correctly.
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}
