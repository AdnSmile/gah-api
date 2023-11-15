<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKamar extends Model {

    use HasFactory;

    protected $table = 'transaksi_kamar';

    protected $primaryKey = 'id_transaksi_kamar';

    protected $fillable = [
      'id_kamar',
      'id_jenis_kamar',
      'id_reservasi',
      'tipe_bed',
      'harga_per_malam',
    ];

    public $timestamps = false;

    public function FKTransaksiKamarInKamar(){
      return $this->belongsTo(Kamar::class, 'id_kamar', 'id_kamar');
  }

  public function FKTransaksiKamarInJenisKamar(){
      return $this->belongsTo(JenisKamar::class, 'id_jenis_kamar', 'id_jenis_kamar');
  }

  public function FKTransaksiKamarInReservasi(){
      return $this->belongsTo(Reservasi::class, 'id_reservasi', 'id_reservasi');
  }
}