<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_invitation', function (Blueprint $table) {
            $table->unsignedBigInteger('team_role_id')->nullable()->after('status');
            $table->foreign('team_role_id')->references('id')->on('team_role')->onDelete('set null');
        });

        if (Schema::hasColumn('team_invitation', 'permissions')) {
            Schema::table('team_invitation', function (Blueprint $table) {
                $table->dropColumn('permissions');
            });
        }
    }

    public function down(): void
    {
        Schema::table('team_invitation', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('status');
            $table->dropForeign(['team_role_id']);
            $table->dropColumn('team_role_id');
        });
    }
};
