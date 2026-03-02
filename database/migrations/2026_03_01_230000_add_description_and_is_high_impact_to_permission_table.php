<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->text('description')->nullable()->after('label');
            $table->boolean('is_high_impact')->default(false)->after('group_name');
        });
    }

    public function down(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_high_impact']);
        });
    }
};
