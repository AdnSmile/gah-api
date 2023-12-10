<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\TransaksiFasilitas;
use App\Models\TransaksiKamar;
use App\Models\Fasilitas;
use Carbon\Carbon;
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
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
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

  public function index() {
    $reservasi = Reservasi::with(
      'FKReservasiInCustomer',
      'FKReservasiInPIC',
      'FKReservasiInFO',
      'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
      'FKReservasiInInvoice',
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
      )->orderBy('created_at', 'desc')->get();

      return response()->json([
        'message' => 'Data akun ditemukan',
        'status' => "success",
        'data' => $reservasi
      ], 200);
  }

  private function getNewIdBooking($prefix, $tglBooking) {
    $tglBookingFormatted = date("dmy", strtotime($tglBooking));
    $idTerbaru = Reservasi::orderBy('tgl_reservasi', 'desc')->first()->id_booking;
    $tigaDigitTerakhir = intval(substr($idTerbaru, -3));
    $tigaDigitTerakhir++;

    $idBooking = $prefix . $tglBookingFormatted . '-' . str_pad($tigaDigitTerakhir, 3, '0', STR_PAD_LEFT);
    return $idBooking;
  }

  private function addNewBooking(Request $req, $idCustomer, $idSM) {
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

    $checkin = Carbon::parse($req->tgl_checkin);
    $checkout = Carbon::parse($req->tgl_checkout);
    $jumlahHari = $checkin->diffInDays($checkout);

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

      $totalPembayaran += $k['jumlah'] * ($k['harga_per_malam'] * $jumlahHari);
    }

    // Check input fasilitas
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
    
    if (Auth::user()->role == 'sm') {
      // Reservasi group
      $idBooking = $this->getNewIdBooking('G', Date('Y-m-d'));
    } else {
      // personal
      $idBooking = $this->getNewIdBooking('P', Date('Y-m-d'));
    }
    $reservasi = Reservasi::create([
      'id_booking' => $idBooking,
      'id_customer' => $idCustomer,
      'id_pic' => $idSM,
      'jumlah_anak' => $req->jumlah_anak,
      'jumlah_dewasa' => $req->jumlah_dewasa,
      'permintaan_khusus' => $req->permintaan_khusus,
      'tgl_checkin' => date("Y-m-d", strtotime($req->tgl_checkin)),
      'tgl_checkout' => date("Y-m-d", strtotime($req->tgl_checkout)),
      'tgl_reservasi' => date('Y-m-d H:i:s'),
      'status' => 'Menunggu Pembayaran',
      'total_deposit' => 0,
      'total_pembayaran' => $totalPembayaran, // hanya kamar, sisanya di invoice
      'updated_at' => null,
      'total_layanan' => $totalHargaLayanan,
    ]);

    // Insert ke kamar
    foreach ($kamar as $kam) {
      for ($i = 0; $i < $kam['jumlah']; $i++) {
        TransaksiKamar::create([
          'id_jenis_kamar' => $kam['id_jenis_kamar'],
          'harga_per_malam' => $kam['harga_per_malam'],
          'id_reservasi' => Reservasi::orderBy('tgl_reservasi', 'desc')->first()->id_reservasi,
        ]);
      }
    }

    // Insert ke fasilitas
    foreach ($fasilitas as $fas) {
      // $harga_layanan = Fasilitas::find($fas['id_layanan'])->harga;
      $layanan = Fasilitas::where('id_layanan', $fas['id_layanan'])->first();

      $reservasi = Reservasi::orderBy('tgl_reservasi', 'desc')->first();

      TransaksiFasilitas::create([
        'jumlah' => $fas['jumlah'],
        'id_reservasi' => $reservasi->id_reservasi,
        'sub_total' => $fas['jumlah'] * $layanan->tarif_layanan,
        'tgl_menerima' => date("Y-m-d", strtotime($req->tgl_checkin)),
        'id_layanan' => $fas['id_layanan'],
      ]);

      // $reservasi->total_pembayaran += $fas['jumlah'] * $layanan->tarif_layanan;
      // $reservasi->save();
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

    if (!$reservasi->id_pic){
      if ($req->uang_jaminan < $reservasi->total_pembayaran) {
        return response()->json([
          'message' => 'Uang jaminan personal harus sama dengan total pembayaran',
          'status' => "error",
          'data' => null
        ], 400);
      }
    }

    $minimumPay = $reservasi->total_pembayaran * 0.5;

    if ($req->uang_jaminan < $minimumPay) {
      return response()->json([
        'message' => 'Uang jaminan minimal 50% dari total pembayaran',
        'status' => "error",
        'data' => null
      ], 400);
    }
    
    $reservasi->uang_jaminan = $req->uang_jaminan;
    $reservasi->status = "Sudah dibayar";
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
        'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
        )
          ->where('id_customer', $idCustomer)
          ->where('tgl_checkin', '>', date('Y-m-d'))
          ->where('status', '!=', 'Check In')
          ->where('status', '!=', 'Batal')
          ->get();
    } else {
      $reservasi = Reservasi::with(
        'FKReservasiInCustomer',
        'FKReservasiInPIC',
        'FKReservasiInFO',
        'FKReservasiInFasilitas.FKTransaksiFasilitasInFasilitas',
        'FKReservasiInInvoice',
        'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
        )
          ->whereNotNull('id_pic')
          ->where('tgl_checkin', '>', date('Y-m-d'))
          ->where('status', '!=', 'Check In')
          ->where('status', '!=', 'Batal')
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

    $reservasi->status = 'Batal';
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
    $idSM = Auth::user()->id_account;

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
      'FKReservasiInTransaksiKamar.FKTransaksiKamarInJenisKamar:id_jenis_kamar,nama'
      )
        ->where('status', 'Menunggu Pembayaran')->orderBy('created_at', 'desc')
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
    $idCustomer = Auth::user()->id_customer;
    
    return $this->getListPembatalan($req, $idCustomer);
  }
  
}