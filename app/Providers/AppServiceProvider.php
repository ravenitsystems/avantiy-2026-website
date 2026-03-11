<?php

namespace App\Providers;

use App\Services\Translations;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Merge database-backed translations into the translator for the current locale.
        $translator = App::get('translator');
        $locale = $translator->getLocale();

        $lines = Translations::getForLocale($locale);
        if (! empty($lines)) {
            $translator->addLines($lines, $locale, '*');
        }
    }
}

