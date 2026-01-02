<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Tambahkan ini

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- PENJADWAL OTOMATIS ---
// Menjalankan command 'cuti:sync' setiap 10 menit
Schedule::command('cuti:sync')->everyTenMinutes();