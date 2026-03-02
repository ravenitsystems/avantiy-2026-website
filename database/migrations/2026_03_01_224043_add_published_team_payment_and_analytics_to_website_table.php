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
            $table->boolean('published')->default(false)->after('accessed_at');
            $table->unsignedBigInteger('team_id')->nullable()->after('user_id');
            $table->date('payment_until')->nullable()->after('team_id');
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_until');
            $table->string('payment_term', 32)->nullable()->after('payment_amount');
            $table->string('google_analytics_measurement_id', 32)->nullable()->after('payment_term');
            $table->string('google_analytics_property_id', 64)->nullable()->after('google_analytics_measurement_id');
            $table->string('google_tag_manager_container_id', 32)->nullable()->after('google_analytics_property_id');

            $table->foreign('team_id')->references('id')->on('team');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn([
                'published',
                'team_id',
                'payment_until',
                'payment_amount',
                'payment_term',
                'google_analytics_measurement_id',
                'google_analytics_property_id',
                'google_tag_manager_container_id',
            ]);
        });
    }
};
