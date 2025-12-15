<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_cuti', function (Blueprint $table) {
            $table->id();
            $table->string('no_urut')->nullable();
            $table->string('nip')->index();
            $table->string('nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('unit_kerja')->nullable();
            $table->string('jenis_cuti');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('lama_cuti');
            $table->string('sisa_cuti_di_excel')->nullable();
            $table->string('no_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('riwayat_cuti');
    }
};