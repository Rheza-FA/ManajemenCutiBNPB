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
        
        // Helper: Hitung Pemakaian Cuti Tahunan
        $hitungPakai = function($tahun) use ($allRiwayat) {
            return $allRiwayat->filter(function($item) use ($tahun) {
                try { $date = Carbon::parse($item->tanggal_mulai); } catch (\Exception $e) { return false; }
                return $date->year == $tahun && trim(strtolower($item->jenis_cuti)) === 'cuti tahunan';
            })->sum('lama_cuti');
        };

        // Helper: Cek Cuti Besar
        // Mengecek apakah ada "Cuti Besar" di tahun tersebut
        $cekCutiBesar = function($tahun) use ($allRiwayat) {
            return $allRiwayat->contains(function ($item) use ($tahun) {
                try { $date = Carbon::parse($item->tanggal_mulai); } catch (\Exception $e) { return false; }
                // Cek string "Besar" (case insensitive) pada jenis cuti
                return $date->year == $tahun && stripos($item->jenis_cuti, 'besar') !== false;
            });
        };

        // 3. LOGIKA BERANTAI
        $jatahDasar = 12;

        // T-2
        $yearA = $targetYear - 2;
        // Jika tahun T-2 ambil Cuti Besar, sisa hangus (0)
        if ($cekCutiBesar($yearA)) {
            $sisaA = 0;
        } else {
            $usageA = $hitungPakai($yearA);
            $sisaA = $jatahDasar - $usageA;
        }
        $carryOverToB = max(0, min($sisaA, 6)); 

        // T-1
        $yearB = $targetYear - 1;
        // Jika tahun T-1 ambil Cuti Besar, sisa hangus (0)
        if ($cekCutiBesar($yearB)) {
            $sisaB = 0;
        } else {
            $usageB = $hitungPakai($yearB);
            $totalQuotaB = $jatahDasar + $carryOverToB;
            $sisaB = $totalQuotaB - $usageB;
        }
        $carryOverTahunLalu = max(0, min($sisaB, 6)); 

        // Target Year (T)
        $cutiTerpakai = $hitungPakai($targetYear);
        $totalJatah = $jatahDasar + $carryOverTahunLalu;
        
        $ambilCutiBesarTahunIni = $cekCutiBesar($targetYear);

        if ($ambilCutiBesarTahunIni) {
            // Jika tahun ini ambil Cuti Besar:
            // Sisa Cuti Tahunan HANGUS (Jadi 0)
            // Carry Over ke tahun depan juga HANGUS (Jadi 0)
            $sisaCuti = 0;
            $carryOverTahunDepan = 0;
        } else {
            $sisaCuti = $totalJatah - $cutiTerpakai;
            $carryOverTahunDepan = max(0, min($sisaCuti, 6));
        }

        // 4. CHART DATA
        // Donut (Bulanan)
        $monthlyUsage = array_fill(1, 12, 0); 
        foreach($allRiwayat as $item) {
            try {
                $date = Carbon::parse($item->tanggal_mulai);
                if($date->year == $targetYear && trim(strtolower($item->jenis_cuti)) === 'cuti tahunan') {
                    $monthlyUsage[$date->month] += $item->lama_cuti;
                }
            } catch (\Exception $e) {}
        }

        // Line (Jenis Cuti)
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

        // 5. FORMATTING
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