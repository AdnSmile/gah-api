<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Invoice extends Model {

  use HasFactory;

    protected $table = 'invoice';

    protected $primaryKey = 'id';
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
      'tgl_pelunasan',
      'total_harga',
      'total_layanan',
      'total_pajak',
      'total_semua',
      'id_fo',
      'total_kamar',
      'updated_at',
      'created_at',
      'id_invoice'
    ];

    public function getInvoiceTotalsByMonth($year) {

      return $this->select(
        DB::raw("MONTHNAME(tgl_pelunasan) AS bulan"),
        DB::raw("SUM(CASE WHEN LEFT(id_invoice, 1) = 'G' THEN total_semua ELSE 0 END) AS grup"),
        DB::raw("SUM(CASE WHEN LEFT(id_invoice, 1) = 'P' THEN total_semua ELSE 0 END) AS personal"),
        DB::raw("SUM(total_semua) AS total")
      )
      ->whereYear('tgl_pelunasan', $year)
      ->groupBy(DB::raw("bulan"), DB::raw("MONTH(tgl_pelunasan)"))
      ->orderBy(DB::raw("MONTH(tgl_pelunasan)"))
      ->get();
    }
}