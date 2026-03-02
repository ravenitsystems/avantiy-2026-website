<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_member', function (Blueprint $table) {
            $table->unsignedBigInteger('team_role_id')->nullable()->after('user_id');
            $table->foreign('team_role_id')->references('id')->on('team_role')->onDelete('set null');
        });

        if (Schema::hasColumn('team_member', 'role')) {
            Schema::table('team_member', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        Schema::table('team_member', function (Blueprint $table) {
            $table->string('role', 32)->default('member')->after('user_id');
            $table->dropForeign(['team_role_id']);
            $table->dropColumn('team_role_id');
        });
    }
};
