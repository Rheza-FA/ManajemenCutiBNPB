<?php

namespace App\Imports;

use App\Models\PegawaiCpns;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PegawaiCpnsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['nip'])) return null;

        return PegawaiCpns::updateOrCreate(
            ['nip' => preg_replace('/[^0-9]/', '', $row['nip'])],
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
                'eselon'            => $row['eselon'],
                
                // Fix Mapping CPNS
                'eselon_1'          => $row['unit_kerja'], 
                'eselon_2'          => $row['unnamed_12'] ?? null,
                'eselon_3'          => $row['unnamed_13'] ?? null,
                'eselon_4'          => $row['unnamed_14'] ?? null,

                'email_pribadi'     => $row['email_pribadi'],
                'jatah_cuti_tahunan'=> 12,
                'sisa_cuti_tahun_lalu' => 0
            ]
        );
    }
}