<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {

  use HasFactory;

    protected $table = 'invoice';

    protected $primaryKey = 'id_invoice';
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
      'tgl_pelunasan',
      'total_harga',
      'total_layanan',
      'total_pajak',
      'total_semua',
      'id_internal'
    ];
}