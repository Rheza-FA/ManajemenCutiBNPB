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
use PhpOffice\PhpWord\TemplateProcessor; 

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

    // --- [FITUR DOWNLOAD WORD] ---
    public function cetakSurat($id)
    {
        // 1. Cari data riwayat cuti
        $riwayat = RiwayatCuti::find($id);

        if (!$riwayat) {
            return redirect()->back()->with('error', 'Data cuti tidak ditemukan.');
        }

        // 2. Cari data pegawai & Tentukan Status
        $pegawai = PegawaiPns::where('nip', $riwayat->nip)->first();
        $statusPegawai = 'PNS';
        
        if (!$pegawai) {
            $pegawai = PegawaiCpns::where('nip', $riwayat->nip)->first();
            $statusPegawai = 'CPNS';
        }
        if (!$pegawai) {
            $pegawai = PegawaiPppk::where('nip', $riwayat->nip)->first();
            $statusPegawai = 'PPPK';
        }

        // 3. Lokasi Template
        $pathTemplate = storage_path('app/templates/template_cuti_tahunan.docx');

        if (!file_exists($pathTemplate)) {
            return redirect()->back()->with('error', 'File template_cuti_tahunan.docx belum ada di folder storage/app/templates/');
        }

        // 4. Proses Pengisian Data
        $template = new TemplateProcessor($pathTemplate);
        Carbon::setLocale('id'); 

        // -- LOGIKA TANGGAL --
        $tglMulai = Carbon::parse($riwayat->tanggal_mulai);
        $tglSelesai = Carbon::parse($riwayat->tanggal_selesai);
        $tglSurat = Carbon::now()->translatedFormat('d F Y');
        $tahunCuti = $tglMulai->year;

        // Tampilan Tanggal
        if ($riwayat->lama_cuti <= 1) {
            $tanggalCutiDisplay = $tglMulai->translatedFormat('d F Y');
        } else {
            if ($tglMulai->month == $tglSelesai->month) {
                 $tanggalCutiDisplay = $tglMulai->format('d') . ' s.d ' . $tglSelesai->translatedFormat('d F Y');
            } else {
                 $tanggalCutiDisplay = $tglMulai->translatedFormat('d F Y') . ' s.d ' . $tglSelesai->translatedFormat('d F Y');
            }
        }

        // -- NOMOR SURAT --
        $nomorSurat = "......./20/SDMUM/SD.10.01/" . date('m') . "/" . date('Y');

        // -- LOGIKA PANGKAT/GOLONGAN --
        $teksPangkat = '-';
        if ($statusPegawai == 'PPPK') {
            $rawGrade = $pegawai->grade ?? $pegawai->gol_ruang_pangkat ?? '-';
            $teksPangkat = ($rawGrade !== '-') ? $rawGrade . ' (PPPK)' : '-';
        } else {
            $kode = $pegawai->gol_ruang_pangkat ?? '-';
            $teksPangkat = $this->konversiPangkat($kode);
        }

        // -- LOGIKA UNIT KERJA --
        $unitKerja = $pegawai->unit_kerja 
                  ?? $pegawai->unit 
                  ?? $pegawai->lokasi_kerja 
                  ?? 'Badan Nasional Penanggulangan Bencana';

        // -- TEMBUSAN --
        $tembusanManual = "........................................................";

        // -- REPLACE VARIABEL WORD --
        $template->setValue('nomor_surat', $nomorSurat);
        $template->setValue('nama', $pegawai->nama ?? '-');
        $template->setValue('nip', $pegawai->nip ?? '-');
        $template->setValue('pangkat', $teksPangkat);
        $template->setValue('jabatan', $pegawai->jabatan ?? '-');
        $template->setValue('tembusan', $tembusanManual);
        $template->setValue('unit_kerja', $unitKerja);

        $template->setValue('tahun_cuti', $tahunCuti . ' '); 
        $template->setValue('lama_cuti', $riwayat->lama_cuti);
        $template->setValue('terbilang', ucwords($this->terbilang($riwayat->lama_cuti)));
        $template->setValue('tanggal_cuti', $tanggalCutiDisplay);
        $template->setValue('tgl_surat', $tglSurat);

        // 5. Simpan & Download
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '_', $pegawai->nama); 
        $tglCutiFile = $tglMulai->format('d-m-Y'); 
        $tempFileName = "Surat_Cuti_{$cleanName}_{$tglCutiFile}.docx";
        
        $tempPath = storage_path('app/public/' . $tempFileName);
        $template->saveAs($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    // --- HELPER FUNCTIONS ---

    private function konversiPangkat($kode) {
        if (!$kode || $kode == '-') return '-';
        $kodeClean = trim(str_replace([' ', '.'], '', strtolower($kode))); 

        $pangkatMap = [
            'i/a' => 'Juru Muda (I/a)', 'ia' => 'Juru Muda (I/a)',
            'i/b' => 'Juru Muda Tingkat I (I/b)', 'ib' => 'Juru Muda Tingkat I (I/b)',
            'i/c' => 'Juru (I/c)', 'ic' => 'Juru (I/c)',
            'i/d' => 'Juru Tingkat I (I/d)', 'id' => 'Juru Tingkat I (I/d)',
            'ii/a' => 'Pengatur Muda (II/a)', 'iia' => 'Pengatur Muda (II/a)',
            'ii/b' => 'Pengatur Muda Tingkat I (II/b)', 'iib' => 'Pengatur Muda Tingkat I (II/b)',
            'ii/c' => 'Pengatur (II/c)', 'iic' => 'Pengatur (II/c)',
            'ii/d' => 'Pengatur Tingkat I (II/d)', 'iid' => 'Pengatur Tingkat I (II/d)',
            'iii/a' => 'Penata Muda (III/a)', 'iiia' => 'Penata Muda (III/a)',
            'iii/b' => 'Penata Muda Tingkat I (III/b)', 'iiib' => 'Penata Muda Tingkat I (III/b)',
            'iii/c' => 'Penata (III/c)', 'iiic' => 'Penata (III/c)',
            'iii/d' => 'Penata Tingkat I (III/d)', 'iiid' => 'Penata Tingkat I (III/d)',
            'iv/a' => 'Pembina (IV/a)', 'iva' => 'Pembina (IV/a)',
            'iv/b' => 'Pembina Tingkat I (IV/b)', 'ivb' => 'Pembina Tingkat I (IV/b)',
            'iv/c' => 'Pembina Utama Muda (IV/c)', 'ivc' => 'Pembina Utama Muda (IV/c)',
            'iv/d' => 'Pembina Utama Madya (IV/d)', 'ivd' => 'Pembina Utama Madya (IV/d)',
            'iv/e' => 'Pembina Utama (IV/e)', 'ive' => 'Pembina Utama (IV/e)',
        ];

        return $pangkatMap[$kodeClean] ?? $kode; 
    }

    private function terbilang($x) {
        $angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        if ($x < 12) return $angka[$x];
        elseif ($x < 20) return $this->terbilang($x - 10) . " Belas";
        elseif ($x < 100) return $this->terbilang($x / 10) . " Puluh " . $this->terbilang($x % 10);
        return $x;
    }

    // --- [LOGIKA HITUNG CUTI DENGAN BATASAN CPNS 1 JULI 2026] ---
    private function calculateData(Request $request)
    {
        $request->validate(['keyword' => 'required|string']);
        $keyword = trim($request->input('keyword'));
        
        // 1. Cari Pegawai
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

        // 2. Setup Data Dasar
        $allRiwayat = RiwayatCuti::where('nip', $pegawai->nip)->get();
        $targetYear = $request->input('tgl_dari') ? Carbon::parse($request->input('tgl_dari'))->year : Carbon::now()->year;
        
        // 3. [LOGIKA CPNS] Cek apakah perlu dibatasi?
        $isCpnsRestricted = false;
        $cpnsNotice = null;

        if ($jenis === 'CPNS') {
            $batasAktifCuti = Carbon::create(2026, 7, 1, 0, 0, 0); // 1 Juli 2026
            $hariIni = Carbon::now();

            // Jika hari ini belum mencapai 1 Juli 2026
            if ($hariIni->lt($batasAktifCuti)) {
                $isCpnsRestricted = true;
                $cpnsNotice = "Status kepegawaian saat ini adalah CPNS. Hak cuti tahunan (12 hari) baru akan efektif mulai tanggal 1 Juli 2026. Sebelum tanggal tersebut, jatah cuti adalah 0 hari.";
            }
        }

        $hitungPakai = function($tahun) use ($allRiwayat) {
            return $allRiwayat->filter(function($item) use ($tahun) {
                try { $date = Carbon::parse($item->tanggal_mulai); } catch (\Exception $e) { return false; }
                return $date->year == $tahun && trim(strtolower($item->jenis_cuti)) === 'cuti tahunan';
            })->sum('lama_cuti');
        };

        // Variable Default
        $jatahDasar = 0;
        $sisaCuti = 0;
        $totalJatah = 0;
        $carryOverTahunLalu = 0;
        $carryOverTahunDepan = 0;
        $ambilCutiBesarTahunIni = false;
        $cutiTerpakai = 0;

        // 4. Hitung Jatah
        if ($isCpnsRestricted) {
            // Jika CPNS & Belum 1 Juli 2026 -> Semua 0
            $jatahDasar = 0;
            $totalJatah = 0;
            $sisaCuti = 0;
            // Tetap hitung yang terpakai (sekadar info jika ada kesalahan input di DB)
            $cutiTerpakai = $hitungPakai($targetYear);
        } else {
            // Logika Normal (PNS / PPPK / CPNS setelah 2026)
            $cekCutiBesar = function($tahun) use ($allRiwayat) {
                return $allRiwayat->contains(function ($item) use ($tahun) {
                    try { $date = Carbon::parse($item->tanggal_mulai); } catch (\Exception $e) { return false; }
                    return $date->year == $tahun && stripos($item->jenis_cuti, 'besar') !== false;
                });
            };

            $jatahDasar = 12;
            $yearC = $targetYear - 3;
            $sisaC = $cekCutiBesar($yearC) ? 0 : ($jatahDasar - $hitungPakai($yearC));
            $carryOverToA = max(0, min($sisaC, 6)); 

            $yearA = $targetYear - 2;
            $sisaA = $cekCutiBesar($yearA) ? 0 : (($jatahDasar + $carryOverToA) - $hitungPakai($yearA));
            $carryOverToB = max(0, min($sisaA, 6)); 

            $yearB = $targetYear - 1;
            $sisaB = $cekCutiBesar($yearB) ? 0 : (($jatahDasar + $carryOverToB) - $hitungPakai($yearB));
            $carryOverTahunLalu = max(0, min($sisaB, 6)); 

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
        }

        // 5. Chart & Output
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

        $riwayatTable = $allRiwayat->sortByDesc('tanggal_mulai')->values();

        $unitKerjaDisplay = '-';
        if ($jenis == 'PPPK') {
            $unitKerjaDisplay = $pegawai->unit_kerja ?? $pegawai->unit ?? $pegawai->lokasi_kerja ?? '-';
        } else {
            $units = array_filter([$pegawai->unit_kerja, $pegawai->eselon_1, $pegawai->eselon_2, $pegawai->eselon_3]);
            $unitKerjaDisplay = implode(' > ', $units);
        }
        
        $golonganDisplay = '-';
        if($jenis == 'PPPK') {
             $rawGrade = $pegawai->grade ?? $pegawai->gol_ruang_pangkat ?? '-';
             $golonganDisplay = ($rawGrade !== '-') ? $rawGrade . ' (PPPK)' : '-';
        } else {
             $golonganDisplay = $pegawai->gol_ruang_pangkat ?? '-';
        }

        return [
            'pegawai' => (object) [
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'jabatan' => $pegawai->jabatan,
                'unit_kerja' => $unitKerjaDisplay,
                'jenis' => $jenis,
                'golongan' => $golonganDisplay
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
            'chart_line' => $lineChartData,
            
            // Variabel Notifikasi
            'cpns_notice' => $cpnsNotice 
        ];
    } 
}