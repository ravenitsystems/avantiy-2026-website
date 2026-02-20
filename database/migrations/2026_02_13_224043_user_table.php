<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigInteger("id")->unsigned()->autoIncrement();
            $table->timestamp('date_created')->nullable();
            $table->timestamp('date_last_login')->nullable();
            $table->string('admin_code', 16)->default('');
            $table->enum('pricing_group', ['standard'])->default('standard');
            $table->string('duda_username', 128)->nullable();
            $table->string('stripe_username', 32)->nullable();
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('email', 128);
            $table->string('password', 128);
            $table->bigInteger('country_id')->unsigned();
            $table->string('telephone', 32);
            $table->boolean('marketing');
            $table->text('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->foreign('country_id')->references('id')->on('country');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user');
        Schema::enableForeignKeyConstraints();
    }
};
