<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_log', function (Blueprint $table) {
            $table->id();
            $table->string('api_name', 8);
            $table->timestamp('timestamp');
            $table->string('url');
            $table->string('method');
            $table->text('headers');
            $table->integer('user_id');
            $table->longText('payload_text')->nullable();
            $table->longText('response_text')->nullable();
            $table->integer('response_code')->nullable();
            $table->integer('response_time')->nullable();
            $table->longText('exception')->nullable();
            $table->text('session')->nullable();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('api_log');
        Schema::enableForeignKeyConstraints();
    }
};
