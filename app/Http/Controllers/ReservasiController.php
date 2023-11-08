<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use Illuminate\Http\Request;

class ReservasiController extends Controller {

  public function show($id) {

    $reservasi = Reservasi::with(
      'FKReservasiInCustomer',
      'FKReservasiInPIC',
      'FKReservasiInFO',
      'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
      'FKReservasiInInvoice',
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar, jenis_kamar'
      )->where('id_reservasi', $id)->first();

      if (!$reservasi) {
        return response()->json([
          'message' => 'Data akun ditemukan',
          'status' => "success",
          'data' => null
        ], 404);
      }

      return response()->json([
        'message' => 'Data akun ditemukan',
        'status' => "success",
        'data' => $reservasi
      ], 200);
  }
}