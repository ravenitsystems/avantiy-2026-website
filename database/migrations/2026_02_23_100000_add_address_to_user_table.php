<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('address_line_1', 128)->nullable()->after('telephone');
            $table->string('address_line_2', 128)->nullable()->after('address_line_1');
            $table->string('city', 64)->nullable()->after('address_line_2');
            $table->string('state_region', 64)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state_region');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['address_line_1', 'address_line_2', 'city', 'state_region', 'postal_code']);
        });
    }
};
