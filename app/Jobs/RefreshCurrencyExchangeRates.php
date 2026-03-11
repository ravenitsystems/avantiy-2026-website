<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshCurrencyExchangeRates implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TODO: Refresh exchange rates for enabled currencies.
        // Example: loop enabled currencies, call ExchangeRate::getRate($code), update currency.exchange_rate.
    }
}
