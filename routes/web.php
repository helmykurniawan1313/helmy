<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AssistantController;
use App\Support\PortfolioContent;
use Illuminate\Support\Facades\Route;

$home = fn (string $locale) => view('home', [
    'p' => PortfolioContent::for($locale),
]);

Route::get('/', fn () => $home('en'))->name('home');
Route::get('/id', fn () => $home('id'))->name('home.id');

// English used to live at /en; keep old links working
Route::permanentRedirect('/en', '/');

// Downloadable resume (uploaded or generated in the admin)
Route::get('/resume', function () {
    $path = \App\Support\Resume::activePath();
    abort_unless($path, 404);

    return response()->download(public_path($path), \App\Support\Resume::downloadName(), ['Content-Type' => 'application/pdf']);
})->name('resume');

Route::post('/assistant', AssistantController::class)
    ->middleware('throttle:15,1')
    ->name('assistant');

// ---------- Admin ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'store'])->middleware('throttle:10,1');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::post('logout', [Admin\AuthController::class, 'destroy'])->name('logout');

        Route::get('sections/{section}/{locale?}', [Admin\SectionController::class, 'edit'])->name('sections.edit');
        Route::put('sections/{section}/{locale?}', [Admin\SectionController::class, 'update'])->name('sections.update');
        Route::delete('sections/{section}/{locale?}', [Admin\SectionController::class, 'destroy'])->name('sections.reset');

        Route::get('settings/ai', [Admin\SettingsController::class, 'edit'])->name('settings.ai');
        Route::put('settings/ai', [Admin\SettingsController::class, 'update'])->name('settings.ai.update');
        Route::post('settings/ai/test/{provider}', [Admin\SettingsController::class, 'test'])->middleware('throttle:10,1')->name('settings.ai.test');

        Route::get('resume', [Admin\ResumeController::class, 'edit'])->name('resume');
        Route::post('resume/upload', [Admin\ResumeController::class, 'upload'])->name('resume.upload');
        Route::post('resume/generate', [Admin\ResumeController::class, 'generate'])->middleware('throttle:6,1')->name('resume.generate');
        Route::put('resume/active', [Admin\ResumeController::class, 'activate'])->name('resume.activate');
        Route::delete('resume/{which}', [Admin\ResumeController::class, 'destroy'])->name('resume.destroy');
    });
});
