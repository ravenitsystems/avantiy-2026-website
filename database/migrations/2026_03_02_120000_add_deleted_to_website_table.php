<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website', function (Blueprint $table) {
            $table->boolean('deleted')->default(false)->after('user_id');
            $table->timestamp('deleted_at')->nullable()->after('deleted');
        });
    }

    public function down(): void
    {
        Schema::table('website', function (Blueprint $table) {
            $table->dropColumn(['deleted', 'deleted_at']);
        });
    }
};
