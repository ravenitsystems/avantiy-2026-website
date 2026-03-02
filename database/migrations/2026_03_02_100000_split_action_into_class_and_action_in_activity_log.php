<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('action_class', 64)->nullable()->after('action')->index();
            $table->string('action_name', 64)->nullable()->after('action_class')->index();
        });

        $rows = DB::table('activity_log')->select('id', 'action')->get();
        foreach ($rows as $row) {
            $action = (string) ($row->action ?? '');
            $dot = strpos($action, '.');
            $class = $dot !== false ? substr($action, 0, $dot) : null;
            $name = $dot !== false ? substr($action, $dot + 1) : ($action ?: null);
            if ($class !== null && strlen($class) > 64) {
                $class = substr($class, 0, 64);
            }
            if ($name !== null && strlen($name) > 64) {
                $name = substr($name, 0, 64);
            }
            DB::table('activity_log')->where('id', $row->id)->update([
                'action_class' => $class,
                'action_name' => $name,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['action_class', 'action_name']);
        });
    }
};
