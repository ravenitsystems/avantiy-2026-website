<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string('name', 64);
            $table->string('alpha_2', 2)->index();
            $table->string('alpha_3', 3);
            $table->string('dial_code', 8);
            $table->unsignedTinyInteger('order_index')->default(100);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('country');
        Schema::enableForeignKeyConstraints();
    }
};
