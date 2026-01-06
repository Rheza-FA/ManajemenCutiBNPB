<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CekCutiController;
use App\Http\Controllers\ImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- HALAMAN UTAMA (PUBLIC) ---

// 1. Menampilkan Halaman Awal
Route::get('/', [CekCutiController::class, 'index'])->name('cek-cuti.index');

// 2. Memproses Form Pencarian (POST)
Route::post('/cek-cuti', [CekCutiController::class, 'check'])->name('cek-cuti.check');

// 3. FIX ERROR REFRESH: Menangani jika user refresh halaman hasil (GET /cek-cuti)
Route::get('/cek-cuti', [CekCutiController::class, 'index']);

// 4. Export Excel
Route::post('/cek-cuti/export', [CekCutiController::class, 'export'])->name('cek-cuti.export');

// 5. [BARU] Cetak Surat Cuti
Route::get('/cetak-surat/{id}', [CekCutiController::class, 'cetakSurat'])->name('cetak.surat');


// --- HALAMAN ADMIN (IMPORT DATA) ---

// 1. Menampilkan Halaman Import
Route::get('/import-data', [ImportController::class, 'index'])->name('import.index');

// 2. Memproses Upload File Excel
Route::post('/import-data', [ImportController::class, 'store'])->name('import.store');