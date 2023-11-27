<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

  public function getRoomStatisticsForMonth($year, $month)
  {
      return DB::table('transaksi_kamar')
          ->join('jenis_kamar', 'transaksi_kamar.id_jenis_kamar', '=', 'jenis_kamar.id_jenis_kamar')
          ->join('reservasi', 'transaksi_kamar.id_reservasi', '=', 'reservasi.id_reservasi')
          ->select(
              'jenis_kamar.id_jenis_kamar',
              'jenis_kamar.nama as jenis_kamar',
              DB::raw('SUM(CASE WHEN reservasi.id_booking LIKE "P%" THEN 1 ELSE 0 END) as personal'),
              DB::raw('SUM(CASE WHEN reservasi.id_booking LIKE "G%" THEN 1 ELSE 0 END) as grup'),
              DB::raw('COUNT(transaksi_kamar.id_transaksi_kamar) as jumlah')
          )
          ->whereMonth('reservasi.tgl_checkin', $month)
          ->whereYear('reservasi.tgl_checkin', $year)
          ->groupBy('jenis_kamar.id_jenis_kamar', 'jenis_kamar.nama')
          ->get();
  }
}