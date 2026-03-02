<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('template', function (Blueprint $table) {
            $table->timestamp('date_created')->nullable()->after('template_id');
        });

        DB::statement('ALTER TABLE template MODIFY order_offset INT NOT NULL DEFAULT 1000');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template', function (Blueprint $table) {
            $table->dropColumn('date_created');
        });

        DB::statement('ALTER TABLE template MODIFY order_offset INT NOT NULL DEFAULT 0');
    }
};
