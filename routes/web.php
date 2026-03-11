<?php

use App\Http\Controllers\ApiController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

$locales = implode('|', config('locales.supported', ['en']));
$defaultLocale = config('locales.default', 'en');

// Redirect root to default locale
Route::get('/', function () use ($defaultLocale) {
    return redirect("/{$defaultLocale}", 302);
});

// SPA and public pages under locale prefix
Route::prefix('{locale}')
    ->middleware(SetLocale::class)
    ->group(function () use ($locales) {
        Route::get('/', function () {
            return view('dashboard-app', ['locale' => request()->route('locale')]);
        })->where('locale', $locales);

        Route::get('/dashboard/{vue_capture?}', function () {
            return view('dashboard-app', ['locale' => request()->route('locale')]);
        })
            ->where('locale', $locales)
            ->where('vue_capture', '[\/\w\.-]*');

        // Public marketing pages (SPA): serve the app so Vue Router can handle the path
        Route::get('/{path}', function () {
            return view('dashboard-app', ['locale' => request()->route('locale')]);
        })
            ->where('locale', $locales)
            ->where('path', 'ecommerce|ai-assistant|seo|templates|pricing|terms|privacy|saas-agreement');
    });

Route::any('/api/{module}/{call}/{id1?}/{id2?}/{id3?}/{id4?}', [ApiController::class, 'handle']);
