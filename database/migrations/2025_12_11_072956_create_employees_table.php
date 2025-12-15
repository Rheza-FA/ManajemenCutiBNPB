<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique(); // Kunci utama pencarian
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('unit_kerja')->nullable();
            $table->string('email')->nullable();
            $table->string('jenis_pegawai'); // 'PNS', 'CPNS', 'PPPK'
            
            // Kolom spesifik (nullable agar fleksibel)
            $table->string('agama')->nullable();
            $table->string('pangkat_golongan')->nullable(); // Untuk PNS/CPNS
            $table->string('grade')->nullable(); // Untuk PPPK
            
            // Kolom untuk Logika Cuti
            $table->integer('jatah_cuti_tahun_ini')->default(12);
            $table->integer('sisa_cuti_tahun_lalu')->default(0); // Carry Over
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};