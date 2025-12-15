<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_histories', function (Blueprint $table) {
            $table->id();
            $table->string('nip_pegawai'); // Foreign key ke tabel employees (via NIP)
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('jenis_cuti')->default('Cuti Tahunan');
            $table->text('keterangan')->nullable();
            $table->integer('lama_cuti'); // Jumlah hari
            $table->string('status')->default('Disetujui'); // Disetujui, Menunggu, Ditolak
            $table->timestamps();

            // Indexing untuk pencarian cepat
            $table->index('nip_pegawai');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_histories');
    }
};