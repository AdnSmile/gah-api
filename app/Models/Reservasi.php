<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model {

    use HasFactory;

    protected $table = 'reservasi';

    protected $primaryKey = 'id_reservasi';

    protected $fillable = [
      'bukti_pembayaran',
      'id_booking',
      'jumlah_anak',
      'jumlah_dewasa',
      'permintaan_khusus',
      'status',
      'tgl_pembayaran',
      'tgl_checkin',
      'tgl_checkout',
      'tgl_reservasi',
      'total_pembayaran',
      'total_deposit',
      'uang_jaminan',
      'id_customer',
      'id_pic',
      'id_fo',
      'id_invoice',
      'updated_at',
      'created_at',
      'total_layanan'
    ];

    public function FKReservasiInCustomer() {

      return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function FKReservasiInPIC() {

      return $this->belongsTo(Account::class, 'id_pic', 'id_account');
    }

    public function FKReservasiInFO() {

      return $this->belongsTo(Account::class, 'id_fo', 'id_account');
    }

    public function FKReservasiInFasilitas() {

      return $this->hasMany(TransaksiFasilitas::class, 'id_reservasi', 'id_reservasi');
    }

    public function FKReservasiInInvoice() {

      return $this->belongsTo(Invoice::class, 'id_invoice', 'id');
    }

    public function FKReservasiInTransaksiKamar() {

      return $this->hasMany(TransaksiKamar::class, 'id_reservasi', 'id_reservasi');
    }
}