<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; // Tambahkan ini
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use App\Models\RiwayatCuti;
use Carbon\Carbon;

class SyncSimpegCuti extends Command
{
    protected $signature = 'cuti:sync';
    protected $description = 'Sinkronisasi data cuti dari SIMPEG BNPB (Auto-Truncate)';

    public function handle()
    {
        $this->info('🚀 Memulai proses sinkronisasi dengan SIMPEG BNPB (Mode Otomatis)...');

        $username = env('SIMPEG_USER');
        $password = env('SIMPEG_PASS');

        if (!$username || !$password) {
            $this->error('❌ Kredensial SIMPEG belum diset di file .env');
            return;
        }

        // 1. SETUP CLIENT BROWSER
        $cookieJar = new CookieJar();
        $client = new Client([
            'verify' => false,
            'cookies' => $cookieJar,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            ]
        ]);

        // 2. PROSES LOGIN
        $this->line('🔑 Mencoba login...');
        
        try {
            $loginUrl = 'https://simpeg.bnpb.go.id/presensi/index.php/auth/login';
            
            $responseLogin = $client->post($loginUrl, [
                'form_params' => [
                    'loginUsername' => $username,
                    'loginPassword' => $password,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Origin' => 'https://simpeg.bnpb.go.id',
                    'Referer' => 'https://simpeg.bnpb.go.id/presensi/index.php/auth',
                ]
            ]);

            $loginBody = (string) $responseLogin->getBody();
            
            if (strpos($loginBody, 'success:true') === false && strpos($loginBody, '"success":true') === false) {
                 if (strpos($loginBody, '<title>Presensi: Halaman Login</title>') !== false) {
                     $this->error('❌ Login Gagal: Username/Password salah atau Captcha dibutuhkan.');
                     return;
                 }
            }

            $this->info('✅ Login Terkirim. Melanjutkan ke pengambilan data...');

            // 3. REQUEST DATA CUTI
            $targetUrl = 'https://simpeg.bnpb.go.id/presensi/index.php/c_izin/data_izin_cuti';
            
            $responseCuti = $client->get($targetUrl, [
                'query' => [
                    'tglawal'   => '2023-01-01',
                    'tglakhir'  => '2026-12-31', // Pastikan range mencakup tahun depan
                    'limit'     => 10000,
                    'start'     => 0,
                    'jeniscuti' => 0,
                    '_dc'       => time(),
                ],
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => 'https://simpeg.bnpb.go.id/presensi/',
                ]
            ]);

            $rawBody = (string) $responseCuti->getBody();

            if (strpos($rawBody, '<!DOCTYPE HTML') !== false) {
                $this->error('❌ Sesi Login Hilang. Server mengembalikan halaman HTML.');
                return;
            }

            // 4. FIX FORMAT JSON
            $fixedBody = preg_replace('/([{,])\s*([a-zA-Z0-9_]+?)\s*:/', '$1"$2":', $rawBody);
            $data = json_decode($fixedBody, true);

            if ($data === null) {
                $data = json_decode($rawBody, true);
            }

            if ($data === null || !isset($data['rows'])) {
                $this->error('❌ Gagal parsing JSON atau Key "rows" tidak ditemukan.');
                return;
            }

            $rows = $data['rows'];
            $count = count($rows);
            $this->info("📦 Ditemukan {$count} data riwayat cuti dari SIMPEG.");

            // 5. TRANSAKSI DATABASE (TRUNCATE & INSERT)
            // Menggunakan Transaction agar jika insert error, data lama tidak hilang setengah-setengah
            DB::transaction(function () use ($rows, $count) {
                
                $this->warn('♻️  Membersihkan data lama (Truncate)...');
                RiwayatCuti::truncate();
                
                $this->info('💾 Menyimpan data baru...');
                $bar = $this->output->createProgressBar($count);
                $bar->start();

                foreach ($rows as $item) {
                    $tglMulai   = Carbon::createFromFormat('d-m-Y', $item['tglMulai'])->format('Y-m-d');
                    $tglSelesai = Carbon::createFromFormat('d-m-Y', $item['tglSelesai'])->format('Y-m-d');
                    $lama       = (int) filter_var($item['lamaCuti'], FILTER_SANITIZE_NUMBER_INT);

                    // Gunakan CREATE karena tabel sudah kosong (lebih cepat)
                    RiwayatCuti::create([
                        'nip'           => $item['nip'],
                        'nama'          => $item['nama'],
                        'jabatan'       => $item['jabatann'] ?? null,
                        'unit_kerja'    => $item['unitKerja'],
                        'jenis_cuti'    => $item['jenisCuti'],
                        'tanggal_mulai' => $tglMulai,
                        'tanggal_selesai' => $tglSelesai,
                        'lama_cuti'     => $lama,
                        'no_sk'         => $item['noSK'] ?? null,
                        'tanggal_sk'    => (isset($item['tglSK']) && $item['tglSK']) ? Carbon::createFromFormat('d-m-Y', $item['tglSK'])->format('Y-m-d') : null,
                        'sisa_cuti_di_excel' => $item['sisaCuti'] ?? 0,
                    ]);

                    $bar->advance();
                }
                $bar->finish();
            });

            $this->newLine();
            $this->info('🎉 Sinkronisasi Selesai! Data Riwayat Cuti telah diperbarui.');

        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}