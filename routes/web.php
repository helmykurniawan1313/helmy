<?php

use App\Http\Controllers\AssistantController;
use Illuminate\Support\Facades\Route;

$home = function (string $locale) {
    app()->setLocale($locale);

    return view('home', [
        'p' => config('portfolio') + __('portfolio'),
    ]);
};

Route::get('/', fn () => $home('en'))->name('home');
Route::get('/id', fn () => $home('id'))->name('home.id');

// English used to live at /en; keep old links working
Route::permanentRedirect('/en', '/');

Route::post('/assistant', AssistantController::class)
    ->middleware('throttle:15,1')
    ->name('assistant');
