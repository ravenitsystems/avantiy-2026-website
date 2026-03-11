<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['id' => 36,   'name' => 'Australian Dollar',      'code' => 'AUD', 'symbol' => 'A$',  'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 48,   'name' => 'Bahraini Dinar',         'code' => 'BHD', 'symbol' => 'BD',   'decimals' => 3, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 986,  'name' => 'Brazilian Real',         'code' => 'BRL', 'symbol' => 'R$',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 124,  'name' => 'Canadian Dollar',        'code' => 'CAD', 'symbol' => 'C$',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 156,  'name' => 'Chinese Yuan Renminbi',  'code' => 'CNY', 'symbol' => '¥',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 756,  'name' => 'Swiss Franc',            'code' => 'CHF', 'symbol' => 'Fr',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 208,  'name' => 'Danish Krone',           'code' => 'DKK', 'symbol' => 'kr',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 978,  'name' => 'Euro',                   'code' => 'EUR', 'symbol' => '€',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 826,  'name' => 'British Pound Sterling', 'code' => 'GBP', 'symbol' => '£',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 344,  'name' => 'Hong Kong Dollar',       'code' => 'HKD', 'symbol' => 'HK$',  'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 356,  'name' => 'Indian Rupee',           'code' => 'INR', 'symbol' => '₹',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 392,  'name' => 'Japanese Yen',           'code' => 'JPY', 'symbol' => '¥',    'decimals' => 0, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 400,  'name' => 'Jordanian Dinar',        'code' => 'JOD', 'symbol' => 'JD',   'decimals' => 3, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 410,  'name' => 'South Korean Won',       'code' => 'KRW', 'symbol' => '₩',    'decimals' => 0, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 414,  'name' => 'Kuwaiti Dinar',         'code' => 'KWD', 'symbol' => 'KD',   'decimals' => 3, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 484,  'name' => 'Mexican Peso',           'code' => 'MXN', 'symbol' => '$',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 554,  'name' => 'New Zealand Dollar',     'code' => 'NZD', 'symbol' => 'NZ$',  'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 578,  'name' => 'Norwegian Krone',        'code' => 'NOK', 'symbol' => 'kr',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 634,  'name' => 'Qatari Rial',            'code' => 'QAR', 'symbol' => 'QR',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 682,  'name' => 'Saudi Riyal',            'code' => 'SAR', 'symbol' => '﷼',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 752,  'name' => 'Swedish Krona',          'code' => 'SEK', 'symbol' => 'kr',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 702,  'name' => 'Singapore Dollar',       'code' => 'SGD', 'symbol' => 'S$',   'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 764,  'name' => 'Thai Baht',              'code' => 'THB', 'symbol' => '฿',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 949,  'name' => 'Turkish Lira',           'code' => 'TRY', 'symbol' => '₺',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 784,  'name' => 'United Arab Emirates Dirham', 'code' => 'AED', 'symbol' => 'د.إ', 'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 840,  'name' => 'US Dollar',              'code' => 'USD', 'symbol' => '$',    'decimals' => 2, 'exchange_rate' => 1, 'last_processed_at' => null, 'enabled' => true ],
            ['id' => 710,  'name' => 'South African Rand',     'code' => 'ZAR', 'symbol' => 'R',    'decimals' => 2, 'exchange_rate' => 0, 'last_processed_at' => null, 'enabled' => true ],
        ];

        $flagSvgById = null;
        if (Schema::hasColumn('currency', 'flag_svg')) {
            $flagSvgById = require __DIR__ . '/data/CurrencyFlagSvgData.php';
        }

        foreach ($currencies as $row) {
            if ($flagSvgById !== null && array_key_exists($row['id'], $flagSvgById)) {
                $row['flag_svg'] = $flagSvgById[$row['id']];
            }
            DB::table('currency')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
