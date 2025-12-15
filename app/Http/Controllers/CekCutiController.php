<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PegawaiPns;
use App\Models\PegawaiCpns;
use App\Models\PegawaiPppk;
use App\Models\RiwayatCuti;
use Carbon\Carbon;

class CekCutiController extends Controller
{
    public function index()
    {
        return view('cek_cuti', ['data' => null]);
    }

    public function check(Request $request)
    {
        $request->validate(['keyword' => 'required|string']);
        $keyword = trim($request->input('keyword'));
        
        // --- 1. CARI DATA PEGAWAI (PNS -> CPNS -> PPPK) ---
        $pegawai = PegawaiPns::where('nip', $keyword)->orWhere('nama', 'LIKE', "%{$keyword}%")->first();
        $jenis = 'PNS';

        if (!$pegawai) {
            $pegawai = PegawaiCpns::where('nip', $keyword)->orWhere('nama', 'LIKE', "%{$keyword}%")->first();
            $jenis = 'CPNS';
        }
        if (!$pegawai) {
            $pegawai = PegawaiPppk::where('nip', $keyword)->orWhere('nama', 'LIKE', "%{$keyword}%")->first();
            $jenis = 'PPPK';
        }

        if (!$pegawai) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        // --- 2. PERSIAPAN DATA RIWAYAT ---
        // Ambil semua data riwayat pegawai ini sekali saja untuk efisiensi
        $allRiwayat = RiwayatCuti::where('nip', $pegawai->nip)->get();

        // Tentukan Tahun Target (Default Tahun Ini)
        $targetYear = $request->input('tgl_dari') ? Carbon::parse($request->input('tgl_dari'))->year : Carbon::now()->year;
        
        // --- 3. FUNGSI HELPER PERHITUNGAN ---
        // Fungsi untuk menghitung total hari "Cuti Tahunan" pada tahun tertentu
        $hitungPakai = function($tahun) use ($allRiwayat) {
            return $allRiwayat->filter(function($item) use ($tahun) {
                // Pastikan format tanggal valid
                try {
                    $date = Carbon::parse($item->tanggal_mulai);
                } catch (\Exception $e) {
                    return false;
                }

                // Logic Filter: Tahun cocok DAN Jenis Cuti adalah "Cuti Tahunan" (Case insensitive & Trimmed)
                $isTahunSama = $date->year == $tahun;
                
                // Normalisasi string jenis cuti: hilangkan spasi depan/belakang, ubah ke lowercase
                $jenisCutiNormalized = trim(strtolower($item->jenis_cuti));
                $isCutiTahunan = $jenisCutiNormalized === 'cuti tahunan';

                return $isTahunSama && $isCutiTahunan;
            })->sum('lama_cuti');
        };

        // --- 4. LOGIKA BERANTAI (CHAIN CALCULATION) ---
        // Kita hitung dari 2 tahun ke belakang untuk mendapatkan akumulasi Carry Over yang valid
        
        $jatahDasar = 12; // Tetap 12 hari

        // [TAHUN T-2] (Misal target 2025, ini hitung 2023)
        $yearA = $targetYear - 2;
        $usageA = $hitungPakai($yearA);
        // Asumsi start fresh di T-2 (12 hari). 
        // Sisa T-2 = 12 - Pakai. Carry over ke T-1 max 6.
        $sisaA = $jatahDasar - $usageA;
        // Carry over tidak boleh negatif
        $carryOverToB = max(0, min($sisaA, 6)); 

        // [TAHUN T-1] (Misal target 2025, ini hitung 2024)
        $yearB = $targetYear - 1;
        $usageB = $hitungPakai($yearB);
        $totalQuotaB = $jatahDasar + $carryOverToB; // 12 + Sisa dari T-2
        $sisaB = $totalQuotaB - $usageB;
        
        // CARRY OVER TAHUN LALU (YANG AKAN DITAMPILKAN DI VIEW)
        // Ini adalah sisa tahun 2024 yang dibawa ke 2025
        $carryOverTahunLalu = max(0, min($sisaB, 6)); 

        // [TAHUN T / TARGET] (Misal 2025)
        $cutiTerpakai = $hitungPakai($targetYear);
        $totalJatah = $jatahDasar + $carryOverTahunLalu;
        
        $sisaCuti = $totalJatah - $cutiTerpakai;
        
        // Carry Over untuk tahun depan (Prediksi)
        $carryOverTahunDepan = max(0, min($sisaCuti, 6));


        // --- 5. FORMATTING DATA UNTUK VIEW ---
        
        // Data Riwayat untuk Tabel (Hanya tampilkan Cuti Tahunan agar tidak bingung, diurutkan tanggal terbaru)
        $riwayatTable = $allRiwayat->filter(function($item){
             return trim(strtolower($item->jenis_cuti)) === 'cuti tahunan';
        })->sortByDesc('tanggal_mulai')->values();

        // Format Unit Kerja
        $unitKerjaDisplay = '-';
        if ($jenis == 'PPPK') {
            $unitKerjaDisplay = $pegawai->unit;
        } else {
            $units = array_filter([
                $pegawai->unit_kerja, 
                $pegawai->eselon_1, 
                $pegawai->eselon_2, 
                $pegawai->eselon_3
            ]);
            $unitKerjaDisplay = implode(' > ', $units);
        }

        $formattedPegawai = (object) [
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'jabatan' => $pegawai->jabatan,
            'unit_kerja' => $unitKerjaDisplay,
            'jenis' => $jenis
        ];

        $data = [
            'pegawai' => $formattedPegawai,
            
            // Variabel Angka
            'sisa_cuti' => $sisaCuti,
            'jatah_dasar' => $jatahDasar,
            'carry_over_tahun_lalu' => $carryOverTahunLalu,
            'total_jatah' => $totalJatah,
            'cuti_terpakai' => $cutiTerpakai,
            'carry_over_tahun_depan' => $carryOverTahunDepan,
            
            // Variabel List
            'riwayat' => $riwayatTable, 
            'tahun' => $targetYear
        ];

        return view('cek_cuti', compact('data'));
    }
}