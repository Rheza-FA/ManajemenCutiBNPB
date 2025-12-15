<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiPns extends Model {
    use HasFactory;
    protected $table = 'pegawai_pns';
    protected $guarded = ['id'];
}