<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\TransaksiFasilitas;
use App\Models\TransaksiKamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
          'message' => 'Data akun tidak ditemukan',
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

  private function getNewIdBooking($prefix, $tglBooking) {
    $tglBookingFormatted = date("dmy", strtotime($tglBooking));
    $idTerbaru = Reservasi::latest();
    $tigaDigitTerakhir = intval(substr($idTerbaru, -3));
    $tigaDigitTerakhir++;

    $idBooking = $prefix . $tglBookingFormatted . '-' . str_pad($tigaDigitTerakhir, 3, '0', STR_PAD_LEFT);
    return $idBooking;
  }

  private function addNewBooking(Request $req, $idCustomer, $idSM = null) {
    // id customer sudah pasti harus diisi
    // id sm diisi kalau reservasi group

    $validated = [
      'jumlah_anak' => 'required|numeric',
      'jumlah_dewasa' => 'required|numeric',
      'permintaan_khusus' => 'string', // tidak wajib diisi
      'tgl_checkin' => 'required|date',
      'tgl_checkout' => 'required|date',
      'kamar' => 'required|array',
      'fasilitas' => 'array', // tidak wajib diisi
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 422);
    }

    // Check input kamar
    $kamar = $req->kamar;
    $kamarValidated = [
      'id_jenis_kamar' => 'required|numeric',
      'jumlah' => 'required|numeric',
      'harga_per_malam' => 'required|numeric',
    ];

    $totalPembayaran = 0;
    foreach ($kamar as $k) {
      $validator = Validator::make($k, $kamarValidated);
      if ($validator->fails()) {
        return response()->json([
          'message' => 'Terjadi kesalahan',
          'status' => "error",
          'data' => $validator->errors()
        ], 422);
      }

      $totalPembayaran += $k['jumlah'] * $k['harga_per_malam'];
    }

    // Check input fasilitas
    $fasilitas = $req->fasilitas;
    $fasilitasValidated = [
      'id_layanan' => 'required|numeric',
      'jumlah' => 'required|numeric',
    ];

    foreach ($fasilitas as $f) {
      $validator = Validator::make($f, $fasilitasValidated);
      if ($validator->fails()) {
        return response()->json([
          'message' => 'Terjadi kesalahan',
          'status' => "error",
          'data' => $validator->errors()
        ], 422);
      }
    }
    
    if ($idSM) {
      // Reservasi group
      $idBooking = $this->getNewIdBooking('G', $req->tgl_checkin);
    } else {
      // personal
      $idBooking = $this->getNewIdBooking('P', $req->tgl_checkin);
    }
    $reservasi = Reservasi::create([
      'id_booking' => $idBooking,
      'id_customer' => $idCustomer,
      'id_sm' => $idSM,
      'jumlah_anak' => $req->jumlah_anak,
      'jumlah_dewasa' => $req->jumlah_dewasa,
      'permintaan_khusus' => $req->permintaan_khusus,
      'tgl_checkin' => $req->tgl_checkin,
      'tgl_checkout' => $req->tgl_checkout,
      'tgl_reservasi' => date('Y-m-d H:i:s'),
      'status' => 'Menunggu Pembayaran',
      'total_pembayaran' => $totalPembayaran, // hanya kamar, sisanya di invoice
    ]);

    // Insert ke kamar
    foreach ($kamar as $kam) {
      for ($i = 0; $i < $kam['jumlah']; $i++) {
        TransaksiKamar::create([
          'id_jenis_kamar' => $kam['id_jenis_kamar'],
          'harga_per_malam' => $kam['harga_per_malam'],
        ]);
      }
    }

    // Insert ke fasilitas
    foreach ($fasilitas as $fas) {
      TransaksiFasilitas::create([
        'id_layanan' => $fas['id_layanan'],
        'jumlah' => $fas['jumlah'],
      ]);
    }

    return response()->json([
      'message' => 'Reservasi berhasil dibuat',
      'status' => "success",
      'data' => $reservasi
    ], 200);
  }

  private function setPembayaran(Request $req, $idReservasi) {
    $validated = [
      'uang_jaminan' => 'required|numeric',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 422);
    }

    $reservasi = Reservasi::find($idReservasi);

    if (!$reservasi) {
      return response()->json([
        'message' => 'Data reservasi tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $reservasi->uang_jaminan = $req->uang_jaminan;
    $reservasi->tgl_pembayaran = date('Y-m-d H:i:s');

    $reservasi->save();

    return response()->json([
      'message' => 'Pembayaran berhasil dibuat',
      'status' => "success",
      'data' => $reservasi
    ], 200);
  }

  public function getListPembatalan(Request $req, $idCustomer = null) {
    // id customer = null kalau SM (buat ngambil semua tamu group)
    if ($idCustomer) {
      $reservasi = Reservasi::with(
        'FKReservasiInCustomer',
        'FKReservasiInPIC',
        'FKReservasiInFO',
        'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
        'FKReservasiInInvoice',
        'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar, jenis_kamar'
        )
          ->where('id_customer', $idCustomer)
          ->where('tgl_checkin', '>', date('Y-m-d'))
          ->get();
    } else {
      $reservasi = Reservasi::with(
        'FKReservasiInCustomer',
        'FKReservasiInPIC',
        'FKReservasiInFO',
        'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
        'FKReservasiInInvoice',
        'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar, jenis_kamar'
        )
          ->whereNotNull('id_sm')
          ->where('tgl_checkin', '>', date('Y-m-d'))
          ->get();
    }

    return response()->json([
      'message' => 'Berhasil get data reservasi',
      'status' => "success",
      'data' => $reservasi
    ], 200);
  }

  private function batalkanBooking(Request $req, $idReservasi) {
    // Check apakah id reservasi memang boleh dibatalkan
    $reservasi = Reservasi::find($idReservasi);
    if (!$reservasi) {
      return response()->json([
        'message' => 'Data reservasi tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    if ($reservasi->tgl_checkin <= date('Y-m-d')) {
      return response()->json([
        'message' => 'Reservasi tidak dapat dibatalkan',
        'status' => "error",
        'data' => null
      ], 400);
    }

    // Check lagi kalau check in > 7 hari, uang dikembalikan
    $tglCheckin = strtotime($reservasi->tgl_checkin);
    $tglSekarang = strtotime(date('Y-m-d'));
    $diff = $tglCheckin - $tglSekarang;

    if ($diff > 7 * 24 * 60 * 60) {
      $pembatalanMsg = 'Reservasi dibatalkan, uang jaminan dikembalikan';
    } else {
      $pembatalanMsg = 'Reservasi dibatalkan, uang jaminan tidak dikembalikan';
    }

    $reservasi->status = 'batal';
    $reservasi->save();

    return response()->json([
      'message' => $pembatalanMsg,
      'status' => "success",
      'data' => $reservasi
    ], 200);
  }

  public function reservasiPersonal() {
    $idBooking = 'P' . date('dmy') . '-' . rand(100, 999);
    $status = "Menunggu Pembayaran";
  }
  
  // GROUP
  public function createGroup(Request $req, $id_cust) {
    $idCustomer = $id_cust;
    $idSM = Auth::user()->id;

    return $this->addNewBooking($req, $idCustomer, $idSM);
  }

  public function bayarGroup(Request $req, $id_res) {
    return $this->setPembayaran($req, $id_res);
  }

  public function getListPembatalanGroup(Request $req) {
    return $this->getListPembatalan($req);
  }

  public function batalkanGroup(Request $req, $id_res) {
    return $this->batalkanBooking($req, $id_res);
  }

  public function getListBelumDibayar(Request $req) {

    $reservasi = Reservasi::with(
      'FKReservasiInCustomer',
      'FKReservasiInPIC',
      'FKReservasiInFO',
      'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
      'FKReservasiInInvoice',
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar, jenis_kamar'
      )
        ->where('status', 'Menunggu Pembayaran')
        ->get();

    return response()->json([
      'message' => 'Berhasil get data reservasi',
      'status' => "success",
      'data' => $reservasi
    ], 200);
  }

  // PERSONAL
  public function createPersonal(Request $req, $id_cust) {
    $idCustomer = $id_cust;
    $idSM = null;

    return $this->addNewBooking($req, $idCustomer, $idSM);
  }

  public function bayarPersonal(Request $req, $id_res) {
    return $this->setPembayaran($req, $id_res);
  }

  public function batalkanPersonal(Request $req, $id_res) {
    return $this->batalkanBooking($req, $id_res);
  }

  public function getListPembatalanPersonal(Request $req) {
    $idCustomer = Auth::user()->id;
    
    return $this->getListPembatalan($req, $idCustomer);
  }
  
}