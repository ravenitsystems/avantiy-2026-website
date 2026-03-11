<?php

namespace App\Console\Commands;

use App\Services\ExchangeRate;
use Illuminate\Console\Command;

class tempGetRateTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:temp-get-rate-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        print ExchangeRate::getRate('USD') . PHP_EOL;
        print ExchangeRate::getRate('GBP') . PHP_EOL;
        print ExchangeRate::getRate('EUR') . PHP_EOL;


    }
}
