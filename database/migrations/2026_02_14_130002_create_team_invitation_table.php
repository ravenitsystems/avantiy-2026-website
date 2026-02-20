<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_invitation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->string('email', 255);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('invited_by_user_id');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('team')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('invited_by_user_id')->references('id')->on('user')->onDelete('restrict');
            $table->index('team_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('team_invitation');
        Schema::enableForeignKeyConstraints();
    }
};
