<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiFasilitas extends Model {

  use HasFactory;

  protected $table = 'transaksi_layanan';

  protected $primaryKey = 'id_transaksi_layanan';

  protected $fillable = [
    'jumlah',
    'sub_total',
    'tgl_menerima',
    'id_layanan',
    'id_reservasi'
  ];

  public $timestamps = false;

  public function FKTransaksiFasilitasInFasilitas() {

    return $this->belongsTo(Fasilitas::class, 'id_layanan', 'id_layanan');
  }
}