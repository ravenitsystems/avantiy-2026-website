<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_payment_method', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('stripe_payment_method_id', 64)->index();
            $table->string('description', 64);
            $table->string('last_four', 4);
            $table->string('brand', 32);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->unique(['user_id', 'stripe_payment_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_payment_method');
    }
};
