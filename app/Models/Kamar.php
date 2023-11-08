<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'kamar';

    protected $primaryKey = 'id_kamar';
    
    protected $fillable = [
      'id_kamar',
      'id_jenis_kamar',
    ];

    public $timestamps = false;

    public function FKKamarInJenisKamar()
    {
        return $this->hasOne(JenisKamar::class, 'id_jenis_kamar', 'id_jenis_kamar');
    }
}