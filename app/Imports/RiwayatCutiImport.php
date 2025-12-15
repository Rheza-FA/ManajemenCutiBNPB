<?php

namespace App\Imports;

use App\Models\RiwayatCuti;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RiwayatCutiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Validasi dasar
        if (empty($row['nip'])) return null;

        // 1. Bersihkan NIP (Hanya angka)
        $cleanNip = preg_replace('/[^0-9]/', '', $row['nip']);

        // 2. Helper Tanggal (Bulletproof parsing)
        $parseDate = function($val) {
            if (!$val) return null;
            try {
                // Jika Excel Serial Number
                if (is_numeric($val)) {
                    return Date::excelToDateTimeObject($val)->format('Y-m-d');
                }
                
                // Format text dd-mm-yyyy atau dd/mm/yyyy
                $val = trim(str_replace(['/', '.'], '-', $val));
                return Carbon::createFromFormat('d-m-Y', $val)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    return Carbon::parse($val)->format('Y-m-d');
                } catch (\Exception $ex) { return null; }
            }
        };

        // 3. Bersihkan Lama Cuti (Hanya Angka)
        $lamaCuti = 0;
        if (isset($row['lama_cuti'])) {
            $lamaCuti = (int) filter_var($row['lama_cuti'], FILTER_SANITIZE_NUMBER_INT);
        }

        // 4. Bersihkan Jenis Cuti (PENTING)
        $jenisCuti = isset($row['jenis_cuti']) ? trim($row['jenis_cuti']) : 'Cuti Tahunan';

        return new RiwayatCuti([
            'no_urut'       => $row['no'] ?? null,
            'nip'           => $cleanNip,
            'nama'          => $row['nama'] ?? null,
            'jabatan'       => $row['jabatan'] ?? null,
            'unit_kerja'    => $row['unit_kerja'] ?? null,
            'jenis_cuti'    => $jenisCuti, 
            'tanggal_mulai' => $parseDate($row['tanggal_mulai']),
            'tanggal_selesai' => $parseDate($row['tanggal_selesai']),
            'lama_cuti'     => $lamaCuti,
            'sisa_cuti_di_excel' => $row['sisa_cuti'] ?? null,
            'no_sk'         => $row['no_sk'] ?? null,
            'tanggal_sk'    => $parseDate($row['tanggal_sk']),
        ]);
    }
}