<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration replaces a single-column unique index on `batch_code`
     * with a composite unique index on (`farm_id`, `batch_code`). It is
     * idempotent: it checks for index existence before dropping/creating.
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Drop single-column unique index if present
            if (Schema::hasColumn('batches', 'batch_code')) {
                // Use INFORMATION_SCHEMA to inspect indexes without Doctrine DBAL
                try {
                    $dbName = DB::getDatabaseName();
                    $rows = DB::select(
                        "SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols
                        FROM information_schema.statistics
                        WHERE table_schema = ? AND table_name = ?
                        GROUP BY INDEX_NAME",
                        [$dbName, 'batches']
                    );

                    $compositeExists = false;
                    foreach ($rows as $row) {
                        $cols = explode(',', $row->cols);
                        // drop single-column unique on batch_code
                        if (count($cols) === 1 && $cols[0] === 'batch_code') {
                            try {
                                DB::statement("DROP INDEX {$row->INDEX_NAME} ON batches");
                            } catch (\Exception $e) {
                                // ignore
                            }
                        }

                        // detect composite farm_id + batch_code
                        if (count($cols) === 2 && in_array('farm_id', $cols) && in_array('batch_code', $cols)) {
                            $compositeExists = true;
                        }
                    }

                    if (!$compositeExists) {
                        // create composite unique index
                        try {
                            DB::statement('ALTER TABLE batches ADD UNIQUE unique_farm_batch (farm_id, batch_code)');
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                } catch (\Exception $e) {
                    // If INFORMATION_SCHEMA isn't accessible, attempt to create index directly
                    try {
                        DB::statement('ALTER TABLE batches ADD UNIQUE unique_farm_batch (farm_id, batch_code)');
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // drop composite index if exists and re-create single-column unique
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $existingIndexes = array_map(fn($i) => $i->getColumns(), $sm->listTableIndexes('batches'));
                foreach ($existingIndexes as $indexName => $cols) {
                    if (is_array($cols) && count($cols) === 2 && in_array('farm_id', $cols) && in_array('batch_code', $cols)) {
                        // Attempt to drop by index name if possible
                        try {
                            $table->dropUnique($indexName);
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                }
            } catch (\Exception $e) {
                // ignore
            }

            // recreate single-column unique if it doesn't exist
            if (!Schema::hasColumn('batches', 'batch_code')) {
                return;
            }

            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $existingIndexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('batches'));
                if (!in_array('batches_batch_code_unique', $existingIndexes)) {
                    $table->unique('batch_code');
                }
            } catch (\Exception $e) {
                try {
                    $table->unique('batch_code');
                } catch (\Exception $e) {
                    // ignore
                }
            }
        });
    }
};
