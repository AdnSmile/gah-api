<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\JenisKamar;
use App\Models\Kamar;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReservasiKamarController extends Controller {

  public function checkAvability(Request $req) {

    $validated = [
      'tgl_checkin' => 'required',
      'tgl_checkout' => 'required',
      'jumlah_dewasa' => 'required|numeric',
      'jumlah_anak' => 'required|numeric',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $tglCheckin = Carbon::parse($req->tgl_checkin)->format('Y-m-d');
    $tglCheckout = Carbon::parse($req->tgl_checkout)->format('Y-m-d');

    if ($tglCheckout < $tglCheckin) {
      return response()->json([
        'message' => 'Tanggal Checkin tidak boleh lebih besar dari tanggal Checkout',
        'status' => "error",
        'data' => null
      ], 400);
    }

    $jumlahKamar = Kamar::select('jenis_kamar.nama as jenis_kamar', DB::raw('COUNT(*) as jumlah_kamar'), 'jenis_kamar.tarif_dasar', 'kamar.id_jenis_kamar as id_jenis_kamar')
    ->join('jenis_kamar', 'jenis_kamar.id_jenis_kamar', '=', 'kamar.id_jenis_kamar')
    ->groupBy('kamar.id_jenis_kamar', 'jenis_kamar.tarif_dasar')
    ->with('FKKamarInJenisKamar')->get();
    

    $kamarTerpesan = Reservasi::where(function($query) use ($req, $tglCheckin, $tglCheckout) {
      $query->where('tgl_checkin', '<', $tglCheckin)->where('tgl_checkout', '>', $tglCheckout);
    })->OrWhere(function($query) use ($req, $tglCheckin, $tglCheckout) {
      $query->where('tgl_checkin', '<', $tglCheckout)->where('tgl_checkout', '>', $tglCheckout);
    })->OrWhere(function($query) use ($req, $tglCheckin, $tglCheckout){
      $query->where('tgl_checkin', '>=', $tglCheckin)->where('tgl_checkout', '<=', $tglCheckout);
    })->where('status', '!=', 'batal')->get();

    $dalamSeason = Season::select(
      'tarif.id_season as id_season', 'tarif.tarif as perubahan_tarif', 
      'tarif.id_jenis_kamar as id_jenis_kamar', 'season.jenis as jenis_season', 
      'season.nama as nama_season'
      )->join('tarif', 'season.id_season', '=', 'tarif.id_season')->where(function($query) use ($req, $tglCheckin, $tglCheckout) {
        $query->where('season.start_season', '<=', $tglCheckin)->where('season.end_season', '>', $tglCheckin);
      })->get();

      foreach ($jumlahKamar as $jumlah) {
        foreach($dalamSeason as $season) {
          if($season->id_jenis_kamar == $jumlah->id_jenis_kamar) {
            if($season->jenis_season == 'high') {
              $harga = $jumlah->tarif_dasar + $season->perubahan_tarif;
            } else {
              $harga = $jumlah->tarif_dasar - $season->perubahan_tarif;
            }
            $jumlah->nama_season = $season->nama_season;
            $jumlah->tarif_harga = $season->perubahan_tarif;
            $jumlah->jenis_season = $season->jenis_season;
            $jumlah->harga_terbaru = $harga;
          }
        }

        $stringJson = json_encode($jumlah); 
        $data = json_decode($stringJson, true);

        if (count($data) < 8) {
          $jumlah->nama_season = null;
          $jumlah->tarif_harga = null;
          $jumlah->jenis_season = 'normal';
          $jumlah->harga_terbaru = $jumlah->tarif_dasar;
        }
      }

    if ($kamarTerpesan !== null && $kamarTerpesan->count() > 0) {
      
      foreach($kamarTerpesan as $reservasi) {
        foreach($reservasi->FKReservasiInTransaksiKamar as $kamar) {
          $idJenisKamar = $kamar->id_jenis_kamar;
          $jenisKamar = $jumlahKamar->first(function ($item) use ($idJenisKamar) {
            return $item->id_jenis_kamar == $idJenisKamar;
          });

          if ($jenisKamar && $jenisKamar->jumlah_kamar > 0) {
            $jenisKamar->jumlah_kamar -= 1;
          }
        }
      }

      return response()->json([
        'message' => 'Data Jumlah kamar pada jenis tertentu',
        'status' => "success",
        'data' => $jumlahKamar
      ], 200);
    }

    return response()->json([
      'message' => 'Data reservasi belum tersedia',
        'status' => "success",
        'data' => $jumlahKamar
    ], 200);
  }

  
}