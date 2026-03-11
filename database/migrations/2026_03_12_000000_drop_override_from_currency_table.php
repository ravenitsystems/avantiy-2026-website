<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currency', function (Blueprint $table) {
            $table->dropForeign(['override']);
            $table->dropColumn('override');
        });
    }

    public function down(): void
    {
        Schema::table('currency', function (Blueprint $table) {
            $table->unsignedInteger('override')->nullable()->after('enabled');
            $table->foreign('override')->references('id')->on('currency')->nullOnDelete();
        });
    }
};
