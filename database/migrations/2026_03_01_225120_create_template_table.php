<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('template', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->unique();
            $table->boolean('enabled');
            $table->integer('order_offset');
            $table->boolean('front_page');
            $table->string('name', 128);
            $table->string('preview_url', 256);
            $table->string('template_type', 16);
            $table->string('visibility', 16);
            $table->boolean('has_store');
            $table->boolean('has_blog');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template');
    }
};
