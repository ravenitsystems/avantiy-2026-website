<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('team_member_permission');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::create('team_member_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_member_id');
            $table->string('permission', 64);
            $table->timestamps();

            $table->foreign('team_member_id')->references('id')->on('team_member')->onDelete('cascade');
            $table->unique(['team_member_id', 'permission']);
            $table->index('team_member_id');
        });
    }
};
