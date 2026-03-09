<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duda_api_log', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unsigned()->autoIncrement();
            $table->timestamp('timestamp_request')->useCurrent();
            $table->timestamp('timestamp_response')->nullable();
            $table->string('host', 64);
            $table->string('uri', 1024);
            $table->string('method', 16);
            $table->longText('payload')->nullable();
            $table->integer('response_code')->nullable();
            $table->longText('response_data')->nullable();
            $table->integer('response_time')->nullable();
            $table->boolean('valid_json')->default(false);
            $table->string('curl_error', 1024)->nullable();
            $table->string('class_and_method', 64);
            $table->longText('backtrace');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duda_api_log');
    }
};
