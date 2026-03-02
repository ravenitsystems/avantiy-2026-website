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
        Schema::table('user', function (Blueprint $table) {
            $table->enum('account_type', ['personal', 'contractor', 'agency', 'enterprise'])
                ->default('personal')
                ->after('stripe_username');
            $table->date('billed_until')->nullable()->after('account_type');
            $table->decimal('amount_charged', 10, 2)->nullable()->after('billed_until');
            $table->string('payment_term', 32)->nullable()->after('amount_charged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'billed_until', 'amount_charged', 'payment_term']);
        });
    }
};
