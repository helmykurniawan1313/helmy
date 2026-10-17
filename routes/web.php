<?php

use App\Http\Controllers\AssistantController;
use Illuminate\Support\Facades\Route;

$home = function (string $locale) {
    app()->setLocale($locale);

    return view('home', [
        'p' => config('portfolio') + __('portfolio'),
    ]);
};

Route::get('/', fn () => $home('id'))->name('home');
Route::get('/en', fn () => $home('en'))->name('home.en');

Route::post('/assistant', AssistantController::class)
    ->middleware('throttle:15,1')
    ->name('assistant');
