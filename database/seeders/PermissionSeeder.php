<?php

namespace Database\Seeders;

use App\Services\TeamPermissions;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        TeamPermissions::seedPermissions();
        TeamPermissions::seedPresetRoles();
    }
}
