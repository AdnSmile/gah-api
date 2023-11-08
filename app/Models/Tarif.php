<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model {
    use HasFactory;

    protected $table = 'tarif';

    protected $primaryKey = 'id_tarif';
    
    protected $fillable = [
      'id_season', 'id_jenis_kamar', 'tarif'
    ];

    public $timestamps = false;

    public function FkTarifInJenisKamar()
    {
        return $this->belongsTo(JenisKamar::class, 'id_jenis_kamar', 'id_jenis_kamar');
    }

    public function FkTarifInSeason()
    {
        return $this->belongsTo(Season::class, 'id_season', 'id_season');
    }
}