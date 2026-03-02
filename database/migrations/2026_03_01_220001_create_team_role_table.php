<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_preset')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('team')->onDelete('cascade');
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('team_role');
        Schema::enableForeignKeyConstraints();
    }
};
