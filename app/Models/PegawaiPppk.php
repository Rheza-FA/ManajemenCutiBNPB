<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiPppk extends Model {
    use HasFactory;
    protected $table = 'pegawai_pppk';
    protected $guarded = ['id'];
}