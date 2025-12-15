<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiCpns extends Model {
    use HasFactory;
    protected $table = 'pegawai_cpns';
    protected $guarded = ['id'];
}