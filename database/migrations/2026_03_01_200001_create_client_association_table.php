<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_association', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('scope_type', 16);
            $table->unsignedBigInteger('scope_user_id')->nullable();
            $table->unsignedBigInteger('scope_team_id')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('client')->onDelete('cascade');
            $table->foreign('scope_user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('scope_team_id')->references('id')->on('team')->onDelete('cascade');
            $table->unique(['client_id', 'scope_type', 'scope_user_id', 'scope_team_id'], 'client_association_scope_unique');
            $table->index('scope_user_id');
            $table->index('scope_team_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('client_association');
        Schema::enableForeignKeyConstraints();
    }
};
