<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pegawai_cpns', function (Blueprint $table) {
            $table->id();
            $table->string('no_urut')->nullable();
            $table->string('nama');
            $table->string('agama')->nullable();
            $table->string('nip')->unique();
            $table->string('jabatan')->nullable();
            $table->string('tmt_jabatan')->nullable();
            $table->string('gol_ruang_pangkat')->nullable();
            $table->string('tmt_pangkat')->nullable();
            $table->string('lama_dalam_jabatan')->nullable();
            $table->string('status')->nullable();
            $table->string('eselon')->nullable();
            
            // Perbaikan: Eselon 1-4
            $table->string('eselon_1')->nullable();
            $table->string('eselon_2')->nullable();
            $table->string('eselon_3')->nullable();
            $table->string('eselon_4')->nullable();

            $table->string('email_pribadi')->nullable();
            
            $table->integer('jatah_cuti_tahunan')->default(12);
            $table->integer('sisa_cuti_tahun_lalu')->default(0); // CPNS usually starts fresh
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pegawai_cpns');
    }
};