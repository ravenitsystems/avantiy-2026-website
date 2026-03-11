<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = require __DIR__ . '/data/CountryListData.php';
        $flagSvgByAlpha2 = require __DIR__ . '/data/CountryFlagSvgData.php';


        foreach ($countries as $row) {
            if (Schema::hasColumn('country', 'flag_svg')) {
                $row['flag_svg'] = $flagSvgByAlpha2[$row['alpha_2']] ?? null;
            }
            DB::table('country')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
