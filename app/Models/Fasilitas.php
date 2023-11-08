<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fasilitas extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    protected $primaryKey = 'id_layanan';
    
    protected $fillable = [
        'nama_layanan',
        'tarif_layanan',
        'satuan'
    ];
    public $timestamps = false;
}