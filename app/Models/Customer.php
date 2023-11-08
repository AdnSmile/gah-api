<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
      'institusi'
    ];

    public $timestamps = false;

    public function FkCustomerInReservasi() {
      return $this->hasMany(Reservasi::class, 'id_customer', 'id_customer');
    }
}