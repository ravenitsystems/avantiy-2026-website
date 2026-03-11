<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard-app');
});

Route::get('/dashboard/{vue_capture?}', function () {
    return view('dashboard-app');
})->where('vue_capture', '[\/\w\.-]*');

// Public marketing pages (SPA): serve the app so Vue Router can handle the path
Route::get('/{path}', function () {
    return view('dashboard-app');
})->where('path', 'ecommerce|ai-assistant|seo|templates|pricing|terms|privacy|saas-agreement');



Route::any('/api/{module}/{call}/{id1?}/{id2?}/{id3?}/{id4?}', [ApiController::class, 'handle']);
