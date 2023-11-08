<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model {

  use HasFactory;

  protected $table = 'season';

  protected $primaryKey = 'id_season';
  
  protected $fillable = [
    'nama',
    'jenis',
    'start_season',
    'end_season'
  ];

  public $timestamps = false;

  public function FkSeasonInTarif() {
      return $this->hasMany(Tarif::class, 'id_season', 'id_season');
  }
}