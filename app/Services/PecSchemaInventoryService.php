<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Throwable;

class PecSchemaInventoryService
{
    /**
     * @return array{
     *     generated_at: string,
     *     database: array{database_name: string, postgres_version: string, transaction_read_only: bool},
     *     summary: array{tables: int, columns: int, indexes: int},
     *     pec_version: null,
     *     pec_version_candidates: list<array{table_schema: string, table_name: string, column_name: string}>,
     *     tables: list<array<string, mixed>>
     * }
     */
    public function inspect(ConnectionInterface $connection): array
    {
        $connection->getPdo();
        $connection->beginTransaction();

        try {
            $connection->statement('SET TRANSACTION READ ONLY');
            $connection->statement("SET LOCAL statement_timeout TO '60s'");

            $database = $connection->selectOne(<<<'SQL'
                SELECT
                    current_database() AS database_name,
                    current_setting('server_version') AS postgres_version,
                    current_setting('transaction_read_only') AS transaction_read_only
            SQL);

            $columns = $connection->select(<<<'SQL'
                SELECT
                    t.table_schema,
                    t.table_name,
                    t.table_type,
                    c.ordinal_position,
                    c.column_name,
                    c.data_type,
                    c.udt_name,
                    c.is_nullable,
                    c.character_maximum_length,
                    c.numeric_precision,
                    c.numeric_scale
                FROM information_schema.tables AS t
                INNER JOIN information_schema.columns AS c
                    ON c.table_schema = t.table_schema
                   AND c.table_name = t.table_name
                WHERE t.table_schema NOT IN ('pg_catalog', 'information_schema')
                  AND (
                    t.table_name ~ '^tb_(fat|dim|acomp)_' OR
                    t.table_name IN ('tb_equipe', 'tb_unidade_saude', 'tb_cidadao')
                  )
                ORDER BY t.table_schema, t.table_name, c.ordinal_position
            SQL);

            $indexes = $connection->select(<<<'SQL'
                SELECT
                    schemaname AS table_schema,
                    tablename AS table_name,
                    indexname AS index_name,
                    indexdef AS index_definition
                FROM pg_indexes
                WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
                  AND (
                    tablename ~ '^tb_(fat|dim|acomp)_' OR
                    tablename IN ('tb_equipe', 'tb_unidade_saude', 'tb_cidadao')
                  )
                ORDER BY schemaname, tablename, indexname
            SQL);

            $estimates = $connection->select(<<<'SQL'
                SELECT
                    namespace.nspname AS table_schema,
                    relation.relname AS table_name,
                    GREATEST(relation.reltuples, 0)::bigint AS estimated_rows
                FROM pg_class AS relation
                INNER JOIN pg_namespace AS namespace
                    ON namespace.oid = relation.relnamespace
                WHERE namespace.nspname NOT IN ('pg_catalog', 'information_schema')
                  AND relation.relkind IN ('r', 'm', 'v')
                  AND (
                    relation.relname ~ '^tb_(fat|dim|acomp)_' OR
                    relation.relname IN ('tb_equipe', 'tb_unidade_saude', 'tb_cidadao')
                  )
                ORDER BY namespace.nspname, relation.relname
            SQL);

            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();

            throw $exception;
        }

        $tables = [];

        foreach ($columns as $column) {
            $key = $column->table_schema.'.'.$column->table_name;

            $tables[$key] ??= [
                'schema' => (string) $column->table_schema,
                'name' => (string) $column->table_name,
                'type' => (string) $column->table_type,
                'estimated_rows' => null,
                'columns' => [],
                'indexes' => [],
            ];

            $tables[$key]['columns'][] = [
                'position' => (int) $column->ordinal_position,
                'name' => (string) $column->column_name,
                'data_type' => (string) $column->data_type,
                'native_type' => (string) $column->udt_name,
                'nullable' => $column->is_nullable === 'YES',
                'max_length' => $column->character_maximum_length === null ? null : (int) $column->character_maximum_length,
                'precision' => $column->numeric_precision === null ? null : (int) $column->numeric_precision,
                'scale' => $column->numeric_scale === null ? null : (int) $column->numeric_scale,
            ];
        }

        foreach ($indexes as $index) {
            $key = $index->table_schema.'.'.$index->table_name;

            if (! isset($tables[$key])) {
                continue;
            }

            $tables[$key]['indexes'][] = [
                'name' => (string) $index->index_name,
                'definition' => (string) $index->index_definition,
            ];
        }

        foreach ($estimates as $estimate) {
            $key = $estimate->table_schema.'.'.$estimate->table_name;

            if (isset($tables[$key])) {
                $tables[$key]['estimated_rows'] = (int) $estimate->estimated_rows;
            }
        }

        $versionCandidates = [];
        $columnCount = 0;

        foreach ($tables as $table) {
            $columnCount += count($table['columns']);

            foreach ($table['columns'] as $column) {
                if (preg_match('/versao|version/i', $column['name']) !== 1) {
                    continue;
                }

                $versionCandidates[] = [
                    'table_schema' => $table['schema'],
                    'table_name' => $table['name'],
                    'column_name' => $column['name'],
                ];
            }
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'database' => [
                'database_name' => (string) ($database->database_name ?? ''),
                'postgres_version' => (string) ($database->postgres_version ?? ''),
                'transaction_read_only' => filter_var(
                    $database->transaction_read_only ?? false,
                    FILTER_VALIDATE_BOOL,
                ),
            ],
            'summary' => [
                'tables' => count($tables),
                'columns' => $columnCount,
                'indexes' => count($indexes),
            ],
            'pec_version' => null,
            'pec_version_candidates' => $versionCandidates,
            'tables' => array_values($tables),
        ];
    }
}
