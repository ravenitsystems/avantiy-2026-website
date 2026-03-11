<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->job(new \App\Jobs\UpdateTemplates)->twiceDaily(0, 12)->name('update-templates');
        $schedule->job(new \App\Jobs\RefreshSiteImages)->twiceDaily(0, 12)->name('refresh-site-images');
        $schedule->job(new \App\Jobs\RefreshCurrencyExchangeRates)->hourly()->name('refresh-currency-exchange-rates');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
