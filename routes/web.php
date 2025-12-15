<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CekCutiController;
use App\Http\Controllers\ImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Utama: Cek Sisa Cuti (Public)
Route::get('/', [CekCutiController::class, 'index'])->name('cek-cuti.index');
Route::post('/cek-cuti', [CekCutiController::class, 'check'])->name('cek-cuti.check');

// Halaman Admin: Import Data Excel
Route::get('/import-data', [ImportController::class, 'index'])->name('import.index');
Route::post('/import-data', [ImportController::class, 'store'])->name('import.store');