<?php

namespace App\Imports;

use App\Models\PegawaiPppk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PegawaiPppkImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['nip'])) return null;

        return PegawaiPppk::updateOrCreate(
            ['nip' => preg_replace('/[^0-9]/', '', $row['nip'])],
            [
                'no_urut'   => $row['no'],
                'nama'      => $row['nama'],
                'jabatan'   => $row['jabatan'],
                'grade'     => $row['grade'],
                'email_bnpb'=> $row['email_bnpb'],
                'unit'      => $row['unit'],
            ]
        );
    }
}