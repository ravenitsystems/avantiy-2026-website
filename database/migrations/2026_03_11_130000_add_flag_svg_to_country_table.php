<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('country', function (Blueprint $table) {
            $table->longText('flag_svg')->nullable()->after('currency_override');
        });
    }

    public function down(): void
    {
        Schema::table('country', function (Blueprint $table) {
            $table->dropColumn('flag_svg');
        });
    }
};
