<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name', 64);
            $table->string('code', 3)->index();
            $table->string('symbol', 16);
            $table->unsignedTinyInteger('decimals')->default(2);
            $table->decimal('exchange_rate', 18, 8)->default(0);
            $table->timestamp('last_processed_at')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('override')->nullable();

            $table->foreign('override')->references('id')->on('currency')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('currency', function (Blueprint $table) {
            $table->dropForeign(['override']);
        });
        Schema::dropIfExists('currency');
    }
};
