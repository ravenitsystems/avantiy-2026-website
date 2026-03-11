<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_group', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unsigned()->autoIncrement();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->text('svg_logo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_group');
    }
};
