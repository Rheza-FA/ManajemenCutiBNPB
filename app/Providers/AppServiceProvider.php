<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Output\ConsoleOutput;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hanya jalankan logika ini jika aplikasi berjalan di terminal (Console)
        if ($this->app->runningInConsole()) {
            
            // Dengarkan event "CommandStarting"
            Event::listen(CommandStarting::class, function (CommandStarting $event) {
                
                // Cek apakah perintah yang diketik user adalah 'serve'
                if ($event->command === 'serve') {
                    
                    $output = new ConsoleOutput();
                    $output->writeln("\n<fg=yellow>⚡ AUTO-SYNC: Mendeteksi startup aplikasi...</>");
                    $output->writeln("<fg=yellow>🔄 Sedang membersihkan & mengambil data terbaru dari SIMPEG...</>\n");

                    // 1. Jalankan perintah 'cuti:sync'
                    // Ini akan otomatis Truncate -> Login -> Sync
                    Artisan::call('cuti:sync');

                    // 2. Tampilkan output dari proses sync ke layar agar user tahu
                    $output->write(Artisan::output());
                    
                    $output->writeln("\n<fg=green>✅ Data siap! Menyalakan Web Server...</>\n");
                }
            });
        }
    }
}