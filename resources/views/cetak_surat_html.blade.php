<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Cuti</title>
    <style>
        /* --- PENGATURAN HALAMAN CETAK --- */
        @page {
            size: A4;
            /* PENTING: Set margin ke 0 untuk menghilangkan Header/Footer bawaan browser (URL, Tanggal, Judul) */
            margin: 0; 
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.15;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
        }

        /* Container utama surat */
        .sheet {
            width: 210mm; /* Lebar A4 */
            
            /* --- PENGATURAN MARGIN SURAT DISINI --- */
            /* Gunakan padding sebagai pengganti margin halaman */
            padding-top: 5.8cm;      /* Jarak Atas (Space untuk KOP SURAT) - Ubah jika kurang/lebih */
            padding-left: 2.54cm;  /* Margin Kiri */
            padding-right: 2.54cm; /* Margin Kanan */
            padding-bottom: 2.54cm;/* Margin Bawah */
            
            box-sizing: border-box; /* Agar padding dihitung dalam lebar total */
            margin: 0 auto;
        }

        /* --- TYPOGRAPHY & LAYOUT --- */
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .judul-surat {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .nomor-surat {
            font-size: 12pt;
        }

        /* TABEL BIODATA */
        .table-data {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .table-data td {
            vertical-align: top;
            padding: 1px 0;
        }
        .col-label { width: 30%; } 
        .col-separator { width: 3%; text-align: center; }
        .col-value { width: 67%; font-weight: bold; }

        /* ISI SURAT */
        .content-body {
            text-align: justify;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        ol.list-syarat {
            margin-top: 0;
            margin-bottom: 0;
            padding-left: 20px;
        }
        ol.list-syarat li {
            padding-left: 10px;
            margin-bottom: 5px;
        }

        /* TANDA TANGAN */
        .ttd-wrapper {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .ttd-kiri {
            width: 40%;
            font-size: 11pt;
            vertical-align: bottom;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .ttd-kanan {
            width: 45%;
            text-align: left; 
            margin-left: auto;
        }
        .space-ttd {
            height: 70px;
        }

        /* TAMPILAN DILAYAR SAJA (PREVIEW) */
        @media screen {
            body { background: #525659; } /* Latar belakang abu gelap seperti PDF reader */
            .sheet {
                background: white;
                margin: 20px auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.5);
                min-height: 297mm; /* Tinggi A4 */
            }
        }
    </style>
</head>
<body>

    <div class="sheet">
        
        <div class="header">
            <div class="judul-surat">SURAT IJIN CUTI TAHUNAN</div>
            <div class="nomor-surat">Nomor : {{ $nomorSurat }}</div>
        </div>

        <p style="margin-bottom: 5px;">Diberikan cuti tahunan kepada Pegawai Negeri Sipil :</p>

        <table class="table-data">
            <tr>
                <td class="col-label">Nama</td>
                <td class="col-separator">:</td>
                <td class="col-value">{{ $pegawai->nama }}</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $pegawai->nip }}</td>
            </tr>
            <tr>
                <td>Pangkat/golongan ruang</td>
                <td>:</td>
                <td>{{ $teksPangkat }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $pegawai->jabatan }}</td>
            </tr>
            <tr>
                <td>Satuan Organisasi</td>
                <td>:</td>
                <td>{{ $unitKerja }}</td>
            </tr>
        </table>

        <div class="content-body">
            <p>
                Kepada yang bersangkutan diberikan cuti tahunan untuk tahun {{ $tahunCuti }} selama 
                <strong>{{ $riwayat->lama_cuti }} ({{ $terbilang }}) hari kerja</strong> pada tanggal 
                <strong>{{ $tanggalCutiDisplay }}</strong> dengan ketentuan sebagai berikut:
            </p>
            <ol class="list-syarat">
                <li>Sebelum menjalankan cuti tahunan wajib menyerahkan pekerjaannya kepada atasan langsungnya atau pejabat lain yang ditentukan;</li>
                <li>Setelah menjalankan cuti tahunan wajib melapor kepada atasan langsung dan bekerja kembali sebagaimana biasanya.</li>
            </ol>
            <p style="margin-top: 15px;">Demikian surat ijin cuti tahunan ini dibuat untuk dapat digunakan sebagaimana mestinya.</p>
        </div>

        <div class="ttd-wrapper">
            
            <div class="ttd-kiri">
                <div>
                    <strong>Tembusan:</strong>
                    <ol style="margin-top: 2px; padding-left: 18px; margin-bottom: 0;">
                        <li>........................................................;</li>
                        <li>Yang Bersangkutan.</li>
                    </ol>
                </div>
            </div>

            <div class="ttd-kanan">
                <p style="margin-bottom: 5px;">Jakarta, {{ $tglSurat }}</p>
                <p style="margin-top: 0;">Plt. Kepala Biro SDM dan Umum,</p>
                
                <div class="space-ttd"></div> 
                
                <p style="font-weight: bold; text-decoration: underline; margin-bottom: 0;">Yahya Djunaid, S.E., M.Si.</p>
            </div>
        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>