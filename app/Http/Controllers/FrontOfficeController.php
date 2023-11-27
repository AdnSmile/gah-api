<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\TransaksiFasilitas;
use App\Models\TransaksiKamar;
use App\Models\Invoice;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class FrontOfficeController extends Controller {
  
  public function index() {

    $reservasi = Reservasi::with(
      'FKReservasiInCustomer',
      'FKReservasiInPIC',
      'FKReservasiInFO',
      'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
      'FKReservasiInInvoice',
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
    )
      ->where('status', '!=', 'Batal')
      ->orderBy('created_at', 'desc')->get();

    return response()->json([
      'status' => "success",
      'message' => 'Berhasil mengambil data pemesanan',
      'data' => $reservasi
    ], 200);
  }

  public function bisaCheckIn() {

    $reservasi = Reservasi::with(
      'FKReservasiInCustomer',
      'FKReservasiInPIC',
      'FKReservasiInFO',
      'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
      'FKReservasiInInvoice',
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
    )
      ->where('status', '!=', 'Batal')
      ->where('status', '!=', 'Menunggu Pembayaran')
      ->where('status', '!=', 'Check Out')
      ->where('status', '!=', 'Check In')
      ->orderBy('created_at', 'desc')->get();

    return response()->json([
      'status' => "success",
      'message' => 'Berhasil mengambil data pemesanan yang bisa Check In',
      'data' => $reservasi
    ], 200);
  }

  public function showSedangCheckin() {
    $reservasi = Reservasi::with(
      'FKReservasiInCustomer',
      'FKReservasiInPIC',
      'FKReservasiInFO',
      'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
      'FKReservasiInInvoice',
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
    ) 
      ->where('status', '=', 'Check In')
      ->orderBy('created_at', 'desc')->get();

    return response()->json([
      'status' => "success",
      'message' => 'Berhasil mengambil data pemesanan yang sedang Check In',
      'data' => $reservasi
    ], 200);
  }

  public function checkin(Request $req, $idReservasi, $idFo) {

    $validated = [
      'uang_deposit' => 'required|numeric|min:300000'
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
        'status' => "failed",
        'message' => 'Reservasi tidak ditemukan',
        'data' => null
      ], 404);
    }

    if ($reservasi->status == "Check In") {
      return response()->json([
        'status' => "failed",
        'message' => 'Reservasi sudah Check In',
        'data' => null
      ], 400);
    }

    $reservasi->status = "Check In";
    $reservasi->total_deposit = $req->uang_deposit;
    $reservasi->id_fo = $idFo;
    $reservasi->save();

    return response()->json([
      'status' => "success",
      'message' => 'Berhasil melakukan Check In',
      'data' => $reservasi
    ], 200);

  }

  public function checkout(Request $req, $idReservasi, $idFo) {
    
    $reservasi = Reservasi::with(
      'FKReservasiInCustomer',
      'FKReservasiInPIC',
      'FKReservasiInFO',
      'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
      'FKReservasiInInvoice',
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
      )->find($idReservasi);
    
    if (!$reservasi) {
      return response()->json([
        'status' => "failed",
        'message' => 'Reservasi tidak ditemukan',
        'data' => null
      ], 404);
    }

    if (!is_null($reservasi->id_invoice)) {
      return response()->json([
        'status' => "failed",
        'message' => 'Reservasi ini sudah Check Out sebelumnya',
        'data' => null
      ], 400);
    }

    $validated = [
      'uang_pembayaran' => 'required|numeric|min:0'
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 422);
    }

    $total_kamar = $reservasi->total_pembayaran;
    $total_layanan = $reservasi->total_layanan;

    $total_pajak = $total_layanan * 0.1;
    $total_semua = $total_kamar + $total_layanan + $total_pajak;
    $jaminan = $reservasi->uang_jaminan;
    $deposit = $reservasi->total_deposit;
    $total_harga = $total_semua - $jaminan - $deposit;

    if ($req->uang_pembayaran < $total_harga) {
      return response()->json([
        'status' => "failed",
        'message' => 'Uang pembayaran kurang',
        'data' => null
      ], 400);
    }

    if ($reservasi->id_booking[0] == 'G') {
      $idInvoice = $this->getNewIdInvoice('G', $reservasi->tgl_checkout);
    } else {
      $idInvoice = $this->getNewIdInvoice('P', $reservasi->tgl_checkout);
    }

    // return $idInvoice;

    $invoice = Invoice::create([
      'tgl_pelunasan' => date("Y-m-d H:i:s"),
      'total_harga' => $total_harga,
      'id_invoice' => $idInvoice,
      'total_layanan' => $total_layanan,
      'total_pajak' => $total_pajak,
      'total_semua' => $total_semua,
      'total_kamar' => $total_kamar,
      'id_fo' => $idFo,
      'updated_at' => null,
    ]);

    if ($invoice) {
      $reservasi->status = "Check Out";
      $reservasi->id_invoice = Invoice::orderBy('created_at', 'desc')->first()->id;
      $reservasi->id_fo = $idFo;
      $reservasi->save();

      return response()->json([
        'status' => "success",
        'message' => 'Berhasil melakukan Check Out',
        'data' => $reservasi
      ], 200);
    }

    return response()->json([
      'status' => "failed",
      'message' => 'Gagal melakukan Check Out',
      'data' => null
    ], 500);
    
  }

  private function getNewIdInvoice($prefix, $tglBooking) {
    $tglBookingFormatted = date("dmy", strtotime($tglBooking));
    $idTerbaru = Invoice::orderBy('tgl_pelunasan', 'desc')->first()->id_invoice;
    $tigaDigitTerakhir = intval(substr($idTerbaru, -3));
    $tigaDigitTerakhir++;

    $idInvoice = $prefix . $tglBookingFormatted . '-' . str_pad($tigaDigitTerakhir, 3, '0', STR_PAD_LEFT);
    return $idInvoice;
  }

  public function addFasilitasCheckin(Request $req, $idReservasi, $idFo) {

    $reservasi = Reservasi::find($idReservasi);
    
    if (!$reservasi) {
      return response()->json([
        'status' => "failed",
        'message' => 'Reservasi tidak ditemukan',
        'data' => null
      ], 404);
    }
    
    $validated = [
      'fasilitas' => 'required|array',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 422);
    }

    $fasilitas = $req->fasilitas;
    $fasilitasValidated = [
      'id_layanan' => 'required|numeric',
      'jumlah' => 'required|numeric',
    ];

    $totalHargaLayanan = 0;
    foreach ($fasilitas as $f) {
      $validator = Validator::make($f, $fasilitasValidated);
      if ($validator->fails()) {
        return response()->json([
          'message' => 'Terjadi kesalahan',
          'status' => "error",
          'data' => $validator->errors()
        ], 422);
      }

      $layanan = Fasilitas::where('id_layanan', $f['id_layanan'])->first();
      $totalHargaLayanan += $f['jumlah'] * $layanan->tarif_layanan;
    }

    $reservasi->total_layanan += $totalHargaLayanan;
    $reservasi->save();

    // Insert ke fasilitas
    foreach ($fasilitas as $fas) {
      
      $layanan = Fasilitas::where('id_layanan', $fas['id_layanan'])->first();

      TransaksiFasilitas::create([
        'jumlah' => $fas['jumlah'],
        'id_reservasi' => $idReservasi,
        'sub_total' => $fas['jumlah'] * $layanan->tarif_layanan,
        'tgl_menerima' => Date("Y-m-d"),
        'id_layanan' => $fas['id_layanan'],
      ]);

    }

    return response()->json([
      'status' => "success",
      'message' => 'Berhasil menambahkan fasilitas',
      'data' => null
    ], 200);
  }

  public function checkinReservasi(Request $req, $idReservasi) {
    $id_fo = Auth::user()->id_account;

    return $this->checkin($req, $idReservasi, $id_fo);
  }

  public function checkoutReservasi(Request $req, $idReservasi) {
    $id_fo = Auth::user()->id_account;

    return $this->checkout($req, $idReservasi, $id_fo);
  }

  public function addFasilitas(Request $req, $idReservasi) {
    $id_fo = Auth::user()->id_account;

    return $this->addFasilitasCheckin($req, $idReservasi, $id_fo);
  }

}