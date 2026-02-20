<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 255);
            $table->string('domain', 255)->default('');
            $table->string('duda_id', 64)->default('');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('user');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('website');
        Schema::enableForeignKeyConstraints();
    }
};
