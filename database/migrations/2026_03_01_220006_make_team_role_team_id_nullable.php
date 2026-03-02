<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_role', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        Schema::table('team_role', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->change();
            $table->foreign('team_id')->references('id')->on('team')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('team_role', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });

        Schema::table('team_role', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable(false)->change();
            $table->foreign('team_id')->references('id')->on('team')->onDelete('cascade');
        });
    }
};
