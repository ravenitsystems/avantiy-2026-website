<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('permission', 'permission_group_id')) {
            Schema::table('permission', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_group_id')->nullable()->after('id');
            });
        }

        // Migrate existing data: create groups from distinct group_name, link permissions
        $groupNames = DB::table('permission')->distinct()->pluck('group_name')->map(fn ($n) => $n ?? 'Other');
        $groupIdByName = [];

        $nameToKey = [
            'Team Management' => 'team_management',
            'Clients' => 'clients',
            'Websites' => 'websites',
            'Conversation' => 'conversation',
            'Other' => 'other',
        ];

        foreach ($groupNames->unique() as $name) {
            $key = $nameToKey[$name] ?? Str::slug($name);
            $existing = DB::table('permission_group')->where('key', $key)->first();
            if ($existing) {
                $groupIdByName[$name] = $existing->id;
            } else {
                $id = DB::table('permission_group')->insertGetId([
                    'key' => $key,
                    'name' => $name,
                    'description' => null,
                    'sort_order' => match ($name) {
                        'Team Management' => 100,
                        'Clients' => 200,
                        'Websites' => 300,
                        'Conversation' => 400,
                        'Other' => 999,
                        default => 999,
                    },
                    'is_high_impact' => in_array($name, ['Team Management', 'Clients', 'Websites']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $groupIdByName[$name] = $id;
            }
        }

        foreach (DB::table('permission')->get() as $perm) {
            $groupName = $perm->group_name ?? 'Other';
            DB::table('permission')
                ->where('id', $perm->id)
                ->update(['permission_group_id' => $groupIdByName[$groupName] ?? null]);
        }

        // Drop existing FK if present (e.g. from nullOnDelete) so we can make column NOT NULL
        $fkRow = DB::selectOne("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'permission'
            AND COLUMN_NAME = 'permission_group_id' AND REFERENCED_TABLE_NAME = 'permission_group'
        ");
        if ($fkRow) {
            DB::statement("ALTER TABLE permission DROP FOREIGN KEY {$fkRow->CONSTRAINT_NAME}");
        }

        DB::statement('ALTER TABLE permission MODIFY COLUMN permission_group_id BIGINT UNSIGNED NOT NULL');

        $hasFk = DB::selectOne("
            SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'permission'
            AND COLUMN_NAME = 'permission_group_id' AND REFERENCED_TABLE_NAME = 'permission_group'
        ");
        if (! $hasFk) {
            Schema::table('permission', function (Blueprint $table) {
                $table->foreign('permission_group_id')->references('id')->on('permission_group')->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('permission', 'group_name')) {
            Schema::table('permission', function (Blueprint $table) {
                $table->dropColumn('group_name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->string('group_name', 64)->nullable()->after('label');
        });

        $groups = DB::table('permission_group')->get()->keyBy('id');
        foreach (DB::table('permission')->get() as $perm) {
            $groupName = $groups->get($perm->permission_group_id)?->name;
            if ($groupName) {
                DB::table('permission')->where('id', $perm->id)->update(['group_name' => $groupName]);
            }
        }

        Schema::table('permission', function (Blueprint $table) {
            $table->dropConstrainedForeignId('permission_group_id');
            $table->string('group_name', 64)->nullable(false)->change();
        });
    }
};
