<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PegawaiPns;
use App\Models\PegawaiCpns;
use App\Models\PegawaiPppk;
use App\Models\RiwayatCuti;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SisaCutiExport;

class CekCutiController extends Controller
{
    public function index()
    {
        return view('cek_cuti', ['data' => null]);
    }

    public function check(Request $request)
    {
        $data = $this->calculateData($request);
        
        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error'])->withInput();
        }

        return view('cek_cuti', compact('data'));
    }

    public function export(Request $request)
    {
        $data = $this->calculateData($request);

        if (isset($data['error'])) {
            return redirect()->back()->with('error', $data['error']);
        }

        $namaFile = 'Sisa_Cuti_' . $data['pegawai']->nip . '_' . date('Ymd_His') . '.xlsx';
        
        return Excel::download(new SisaCutiExport($data), $namaFile);
    }

   private function calculateData(Request $request)
    {
        $request->validate(['keyword' => 'required|string']);
        $keyword = trim($request->input('keyword'));
        
        // 1. CARI DATA PEGAWAI
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
            return ['error' => 'Pegawai tidak ditemukan.'];
        }

        // 2. SETUP DATA
        $allRiwayat = RiwayatCuti::where('nip', $pegawai->nip)->get();
        $targetYear = $request->input('tgl_dari') ? Carbon::parse($request->input('tgl_dari'))->year : Carbon::now()->year;
        
        $hitungPakai = function($tahun) use ($allRiwayat) {
            return $allRiwayat->filter(function($item) use ($tahun) {
                try { $date = Carbon::parse($item->tanggal_mulai); } catch (\Exception $e) { return false; }
                return $date->year == $tahun && trim(strtolower($item->jenis_cuti)) === 'cuti tahunan';
            })->sum('lama_cuti');
        };

        $cekCutiBesar = function($tahun) use ($allRiwayat) {
            return $allRiwayat->contains(function ($item) use ($tahun) {
                try { $date = Carbon::parse($item->tanggal_mulai); } catch (\Exception $e) { return false; }
                return $date->year == $tahun && stripos($item->jenis_cuti, 'besar') !== false;
            });
        };

        // 3. LOGIKA BERANTAI (DIPERBAIKI: MUNDUR SAMPAI T-3)
        $jatahDasar = 12;

        // --- TAHUN T-3 (Contoh: 2023) ---
        // Kita hitung ini agar T-2 punya modal carry over yang benar
        $yearC = $targetYear - 3;
        if ($cekCutiBesar($yearC)) {
            $sisaC = 0;
        } else {
            $usageC = $hitungPakai($yearC);
            // Asumsi T-3 start flat 12 (karena kita tidak mungkin mundur selamanya)
            $sisaC = $jatahDasar - $usageC; 
        }
        $carryOverToA = max(0, min($sisaC, 6)); // Carry Over ke T-2

        // --- TAHUN T-2 (Contoh: 2024) ---
        $yearA = $targetYear - 2;
        if ($cekCutiBesar($yearA)) {
            $sisaA = 0;
        } else {
            $usageA = $hitungPakai($yearA);
            // Disini perbedaannya: Modal T-2 sekarang 12 + CarryOver dari T-3
            $totalQuotaA = $jatahDasar + $carryOverToA; 
            $sisaA = $totalQuotaA - $usageA;
        }
        $carryOverToB = max(0, min($sisaA, 6)); // Carry Over ke T-1

        // --- TAHUN T-1 (Contoh: 2025) ---
        $yearB = $targetYear - 1;
        if ($cekCutiBesar($yearB)) {
            $sisaB = 0;
        } else {
            $usageB = $hitungPakai($yearB);
            $totalQuotaB = $jatahDasar + $carryOverToB;
            $sisaB = $totalQuotaB - $usageB;
        }
        $carryOverTahunLalu = max(0, min($sisaB, 6)); // INI YANG AKAN TAMPIL DI DASHBOARD

        // --- TAHUN TARGET (Contoh: 2026) ---
        $cutiTerpakai = $hitungPakai($targetYear);
        $totalJatah = $jatahDasar + $carryOverTahunLalu;
        
        $ambilCutiBesarTahunIni = $cekCutiBesar($targetYear);

        if ($ambilCutiBesarTahunIni) {
            $sisaCuti = 0;
            $carryOverTahunDepan = 0;
        } else {
            $sisaCuti = $totalJatah - $cutiTerpakai;
            $carryOverTahunDepan = max(0, min($sisaCuti, 6));
        }

        // 4. CHART & FORMATTING (TETAP SAMA)
        $monthlyUsage = array_fill(1, 12, 0); 
        foreach($allRiwayat as $item) {
            try {
                $date = Carbon::parse($item->tanggal_mulai);
                if($date->year == $targetYear && trim(strtolower($item->jenis_cuti)) === 'cuti tahunan') {
                    $monthlyUsage[$date->month] += $item->lama_cuti;
                }
            } catch (\Exception $e) {}
        }

        $jenisCutiList = $allRiwayat->filter(function($item) use ($targetYear) {
            return Carbon::parse($item->tanggal_mulai)->year == $targetYear;
        })->pluck('jenis_cuti')->unique()->values();

        $lineChartData = [];
        $colors = ['#ff6b00', '#0f3878', '#17c1e8', '#82d616', '#ea0606', '#cb0c9f'];
        $colorIndex = 0;

        foreach($jenisCutiList as $jns) {
            $dataPerBulan = array_fill(1, 12, 0);
            foreach($allRiwayat as $item) {
                $date = Carbon::parse($item->tanggal_mulai);
                if($date->year == $targetYear && trim($item->jenis_cuti) == trim($jns)) {
                    $dataPerBulan[$date->month] += $item->lama_cuti;
                }
            }
            $lineChartData[] = [
                'label' => $jns,
                'data' => array_values($dataPerBulan),
                'borderColor' => $colors[$colorIndex % count($colors)],
                'backgroundColor' => $colors[$colorIndex % count($colors)],
                'tension' => 0.4,
                'fill' => false
            ];
            $colorIndex++;
        }

        $riwayatTable = $allRiwayat->filter(function($item){
             return trim(strtolower($item->jenis_cuti)) === 'cuti tahunan';
        })->sortByDesc('tanggal_mulai')->values();

        $unitKerjaDisplay = '-';
        if ($jenis == 'PPPK') {
            $unitKerjaDisplay = $pegawai->unit;
        } else {
            $units = array_filter([$pegawai->unit_kerja, $pegawai->eselon_1, $pegawai->eselon_2, $pegawai->eselon_3]);
            $unitKerjaDisplay = implode(' > ', $units);
        }

        return [
            'pegawai' => (object) [
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'jabatan' => $pegawai->jabatan,
                'unit_kerja' => $unitKerjaDisplay,
                'jenis' => $jenis
            ],
            'sisa_cuti' => $sisaCuti,
            'jatah_dasar' => $jatahDasar,
            'carry_over_tahun_lalu' => $carryOverTahunLalu,
            'total_jatah' => $totalJatah,
            'cuti_terpakai' => $cutiTerpakai,
            'carry_over_tahun_depan' => $carryOverTahunDepan,
            'status_cuti_besar' => $ambilCutiBesarTahunIni, 
            'riwayat' => $riwayatTable,
            'tahun' => $targetYear,
            'chart_donut' => array_values($monthlyUsage),
            'chart_line' => $lineChartData
        ];
    } 
}