<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard/{vue_capture?}', function () {
    return view('dashboard-app');
})->where('vue_capture', '[\/\w\.-]*');



Route::any('/api/{module}/{call}/{id1?}/{id2?}/{id3?}/{id4?}', [ApiController::class, 'handle']);
