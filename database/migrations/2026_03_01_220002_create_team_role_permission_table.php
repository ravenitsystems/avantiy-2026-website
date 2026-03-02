<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_role_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();

            $table->foreign('team_role_id')->references('id')->on('team_role')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permission')->onDelete('cascade');
            $table->unique(['team_role_id', 'permission_id']);
            $table->index('team_role_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('team_role_permission');
        Schema::enableForeignKeyConstraints();
    }
};
