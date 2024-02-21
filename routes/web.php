<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;

use App\Http\Controllers\RawMaterialController;

Route::get('lang/{locale}', [LanguageController::class, 'swap']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        return view('content.dashboard.dashboard-mdv');
    })->name('dashboard');

    Route::resource('raw-materials', RawMaterialController::class); // Genero automaticamente las rutas para el CRUD de Raw Materials
});
