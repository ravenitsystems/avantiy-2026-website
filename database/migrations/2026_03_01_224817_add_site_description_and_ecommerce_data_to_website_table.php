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
        Schema::table('website', function (Blueprint $table) {
            $table->text('site_description')->nullable()->after('site_name');
            $table->longText('ecommerce_data')->nullable()->after('site_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website', function (Blueprint $table) {
            $table->dropColumn(['site_description', 'ecommerce_data']);
        });
    }
};
