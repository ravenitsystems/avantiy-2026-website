<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('country', function (Blueprint $table) {
            $table->boolean('enabled')->default(true)->after('order_index');
            $table->unsignedInteger('currency')->nullable()->after('enabled');
            $table->unsignedInteger('currency_override')->nullable()->after('currency');

            $table->foreign('currency')->references('id')->on('currency')->nullOnDelete();
            $table->foreign('currency_override')->references('id')->on('currency')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('country', function (Blueprint $table) {
            $table->dropForeign(['currency']);
            $table->dropForeign(['currency_override']);
            $table->dropColumn(['enabled', 'currency', 'currency_override']);
        });
    }
};
