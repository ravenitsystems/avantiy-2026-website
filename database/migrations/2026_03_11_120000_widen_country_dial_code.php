<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `country` MODIFY `dial_code` VARCHAR(16) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `country` MODIFY `dial_code` VARCHAR(8) NOT NULL');
    }
};
