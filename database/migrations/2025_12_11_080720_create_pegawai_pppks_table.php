<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pegawai_pppk', function (Blueprint $table) {
            $table->id();
            $table->string('no_urut')->nullable();
            $table->string('nama');
            $table->string('nip')->unique();
            $table->string('jabatan')->nullable();
            $table->string('grade')->nullable();
            $table->string('email_bnpb')->nullable();
            $table->string('unit')->nullable(); // PPPK Excel header is 'UNIT'
            
            $table->integer('jatah_cuti_tahunan')->default(12);
            $table->integer('sisa_cuti_tahun_lalu')->default(0); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pegawai_pppk');
    }
};