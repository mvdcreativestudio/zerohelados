<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;

Route::get('lang/{locale}', [LanguageController::class, 'swap']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        return view('content.dashboard.dashboard-mvd');
    })->name('dashboard');
});
