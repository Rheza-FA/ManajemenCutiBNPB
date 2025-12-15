<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatCuti extends Model {
    use HasFactory;
    protected $table = 'riwayat_cuti';
    protected $guarded = ['id'];
}