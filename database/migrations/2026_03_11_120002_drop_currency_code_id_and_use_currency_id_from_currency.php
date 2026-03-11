<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'country';
        $columns = ['currency_code_id', 'use_currency_id'];

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $constraintName = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? 
                 AND REFERENCED_TABLE_NAME IS NOT NULL 
                 LIMIT 1",
                [Schema::getConnection()->getDatabaseName(), $table, $column]
            );

            if ($constraintName) {
                Schema::table($table, function (Blueprint $t) use ($constraintName) {
                    $t->dropForeign($constraintName->CONSTRAINT_NAME);
                });
            }

            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropColumn($column);
            });
        }
    }

    public function down(): void
    {
        // Cannot restore dropped columns without original definition
    }
};
