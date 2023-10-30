<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKamar extends Model
{
    use HasFactory;

    protected $table = 'jenis_kamar';

    protected $primaryKey = 'id_jenis_kamar';
    
    protected $fillable = [
      'deskripsi',
      'kapasitas',
      'nama',
      'rincian_kamar',
      'tarif_dasar',
      'tipe_bed',
      'ukuran_kamar'
    ];

    public $timestamps = false;

    public function FKJenisKamarInKamar()
    {
        return $this->hasMany(Kamar::class, 'id_jenis_kamar', 'id_jenis_kamar');
    }
}