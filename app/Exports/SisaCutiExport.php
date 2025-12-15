<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SisaCutiExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Kita hanya mengembalikan koleksi riwayat untuk tabel
        // Data pegawai & sisa cuti akan kita taruh di Heading
        return $this->data['riwayat'];
    }

    public function map($riwayat): array
    {
        return [
            $riwayat->jenis_cuti,
            \Carbon\Carbon::parse($riwayat->tanggal_mulai)->format('d-m-Y'),
            \Carbon\Carbon::parse($riwayat->tanggal_selesai)->format('d-m-Y'),
            $riwayat->lama_cuti . ' hari',
            $riwayat->keterangan ?? '-',
            'Disetujui'
        ];
    }

    public function headings(): array
    {
        $d = $this->data;
        $p = $d['pegawai'];

        return [
            ['LAPORAN SISA CUTI PEGAWAI'],
            [''],
            ['Nama', $p->nama],
            ['NIP', $p->nip],
            ['Jabatan', $p->jabatan],
            ['Unit Kerja', $p->unit_kerja],
            [''],
            ['RINCIAN PERHITUNGAN CUTI (TAHUN ' . $d['tahun'] . ')'],
            ['Jatah Dasar', $d['jatah_dasar'] . ' Hari'],
            ['Carry Over Thn Lalu', $d['carry_over_tahun_lalu'] . ' Hari'],
            ['Total Jatah', $d['total_jatah'] . ' Hari'],
            ['Cuti Diambil', $d['cuti_terpakai'] . ' Hari'],
            ['Sisa Cuti Saat Ini', $d['sisa_cuti'] . ' Hari'],
            ['Carry Over ke Thn Depan', $d['carry_over_tahun_depan'] . ' Hari'],
            [''],
            ['RIWAYAT CUTI TAHUNAN'], // Header Tabel
            [ // Kolom Tabel
                'Jenis Cuti',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Lama Cuti',
                'Keterangan',
                'Status'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Bold untuk Judul Utama
            1 => ['font' => ['bold' => true, 'size' => 14]],
            
            // Bold untuk Label Pegawai & Rincian
            3 => ['font' => ['bold' => true]], 4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]], 6 => ['font' => ['bold' => true]],
            
            8  => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0F3878']]], // Header Section Biru
            16 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFFF6B00']]], // Header Tabel Orange
            
            // Header Kolom Tabel
            17 => ['font' => ['bold' => true], 'borders' => ['bottom' => ['borderStyle' => 'thin']]],
        ];
    }
}