<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('activity_log', 'action_class')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->string('action_class', 64)->nullable()->after('action')->index();
                $table->string('action_name', 64)->nullable()->after('action_class')->index();
            });
        } elseif (! Schema::hasColumn('activity_log', 'action_name')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->string('action_name', 64)->nullable()->after('action_class')->index();
            });
        }

        $hasClass = Schema::hasColumn('activity_log', 'action_class');
        $hasName = Schema::hasColumn('activity_log', 'action_name');
        if ($hasClass || $hasName) {
            $rows = DB::table('activity_log')->select('id', 'action')->get();
            foreach ($rows as $row) {
                $action = (string) ($row->action ?? '');
                $dot = strpos($action, '.');
                $class = $dot !== false ? substr(substr($action, 0, $dot), 0, 64) : null;
                $name = $dot !== false ? substr(substr($action, $dot + 1), 0, 64) : ($action ? substr($action, 0, 64) : null);
                $updates = [];
                if ($hasClass) {
                    $updates['action_class'] = $class;
                }
                if ($hasName) {
                    $updates['action_name'] = $name;
                }
                if ($updates !== []) {
                    DB::table('activity_log')->where('id', $row->id)->update($updates);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumns('activity_log', ['action_class', 'action_name'])) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropColumn(['action_class', 'action_name']);
            });
        }
    }
};
