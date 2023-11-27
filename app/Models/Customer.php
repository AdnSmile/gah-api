<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model {

  use HasFactory;

  protected $table = 'customer';

    protected $primaryKey = 'id_customer';

    protected $fillable = [
      'alamat',
      'nama',
      'email',
      'no_telpon',
      'no_identitas',
      'institusi',
      'updated_at',
      'created_at',
    ];

    public $timestamps = false;

    public function FkCustomerInReservasi() {
      return $this->hasMany(Reservasi::class, 'id_customer', 'id_customer');
    }

    public function getCustomerCountByMonth($year) {

      return $this->select(
        DB::raw("DATE_FORMAT(created_at, '%M') AS nama_bulan"),
        DB::raw("COUNT(*) AS jumlah_customer")
      )
      ->whereYear('created_at', $year)
      ->groupBy(DB::raw("nama_bulan, MONTH(created_at)"))
      ->get();
    }
}