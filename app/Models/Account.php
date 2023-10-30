<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable {

  use HasApiTokens, HasFactory, Notifiable;

  protected $table = 'account';

    protected $primaryKey = 'id_account';

    protected $fillable = [
      'email',
      'nama',
      'password',
      'role',
      'username',
      'id_customer'
    ];

    protected $hidden = [
      'password',
    ];

    public $timestamps = false;

    public function FkAccountInCustomer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }
}