<?php

namespace App\Console\Commands;

use App\Jobs\RefreshCurrencyExchangeRates;
use Illuminate\Console\Command;

class RefreshCurrencyExchangeRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh-currency-exchange-rates {--sync : Run the job immediately instead of dispatching to the queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh exchange rates for enabled currencies (runs the RefreshCurrencyExchangeRates job)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('sync')) {
            (new RefreshCurrencyExchangeRates)->handle();
            $this->info('RefreshCurrencyExchangeRates job ran successfully.');
        } else {
            RefreshCurrencyExchangeRates::dispatch();
            $this->info('RefreshCurrencyExchangeRates job dispatched to the queue.');
        }

        return self::SUCCESS;
    }
}
