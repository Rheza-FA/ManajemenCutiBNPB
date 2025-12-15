<?php

namespace App\Imports;

use App\Models\PegawaiPns;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PegawaiPnsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['nip'])) return null;

        // MAPPING KOLOM ESELON BERDASARKAN URUTAN DI EXCEL
        // Header 'UNIT KERJA' di row 1 menempati kolom pertama grup eselon.
        // Kolom kosong berikutnya (unnamed) adalah eselon 2, 3, 4.
        
        // Logika Mapping:
        // eselon_1 = key 'unit_kerja'
        // eselon_2 = key 'unnamed_12' (biasanya urutan index setelah unit_kerja)
        // eselon_3 = key 'unnamed_13'
        // eselon_4 = key 'unnamed_14'
        
        // *NOTE: Index unnamed bisa berubah tergantung jumlah kolom sebelumnya.
        // Berdasarkan file Anda: NO(0), NAMA(1), Agama(2), NIP(3), JABATAN(4), TMT(5), GOL(6), TMT(7), LAMA(8), STATUS(9), ESELON(10), UNIT(11)
        // Maka: Unit Kerja = 11. Unnamed = 12, 13, 14.
        
        return PegawaiPns::updateOrCreate(
            ['nip' => preg_replace('/[^0-9]/', '', $row['nip'])], // Clean NIP
            [
                'no_urut'           => $row['no'],
                'nama'              => $row['nama'],
                'agama'             => $row['agama'],
                'jabatan'           => $row['jabatan'],
                'tmt_jabatan'       => $row['tmt_jabatan'],
                'gol_ruang_pangkat' => $row['gol_ruang_pangkat'],
                'tmt_pangkat'       => $row['tmt_pangkat'],
                'lama_dalam_jabatan'=> $row['lama_dalam_jabatan'],
                'status'            => $row['status'],
                'eselon'            => $row['eselon'], // Ini Level Eselon (I, II)
                
                // Fix Mapping
                'eselon_1'          => $row['unit_kerja'], 
                'eselon_2'          => $row['unnamed_12'] ?? null,
                'eselon_3'          => $row['unnamed_13'] ?? null,
                'eselon_4'          => $row['unnamed_14'] ?? null,

                'email_bnpb'        => $row['email_bnpb'],
                'email_pribadi'     => $row['email_pribadi'],
                'jatah_cuti_tahunan'=> 12,
                'sisa_cuti_tahun_lalu' => 6 // Default asumsi max
            ]
        );
    }
}