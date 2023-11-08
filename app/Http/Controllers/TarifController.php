<?php

namespace App\Http\Controllers;

use App\Models\Tarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TarifController extends Controller {

  public function index() {

    $tarif = Tarif::with('FkTarifInJenisKamar', 'FkTarifInSeason')->get();

    return response()->json([
      'message' => 'Daftar data tarif',
      'status' => "success",
      'data' => $tarif
    ], 200);
  }

  public function store(Request $req) {

    $validated = [
      'id_season' => 'required|numeric', 
      'id_jenis_kamar' => 'required|numeric', 
      'tarif' => 'required|numeric',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $checkTarif = Tarif::where('id_jenis_kamar', $req->id_jenis_kamar)->where('id_season', $req->id_season)->first();

    if ($checkTarif) {
      return response()->json([
        'message' => 'Data tarif sudah ada',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $tarif = Tarif::create([
      'id_season' => $req->id_season,
      'id_jenis_kamar' => $req->id_jenis_kamar,
      'tarif' => $req->tarif,
    ]);

    if ($tarif) {
      return response()->json([
        'message' => 'Tarif berhasil ditambahkan',
        'status' => "success",
        'data' => $tarif
      ], 200);
    }
  }

  public function show($id) {

    $tarif = Tarif::with('FkTarifInJenisKamar', 'FkTarifInSeason')->find($id);

    if (!$tarif) {
      return response()->json([
        'message' => 'Data tarif tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    return response()->json([
      'message' => 'Data tarif ditemukan',
      'status' => "success",
      'data' => $tarif
    ], 200);
  }

  public function delete($id) {

    $tarif = Tarif::find($id);

    if (!$tarif) {
      return response()->json([
        'message' => 'Data tarif tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $tarif->delete();

    return response()->json([
      'message' => 'Data tarif berhasil dihapus',
      'status' => "success",
      'data' => $tarif
    ], 200);
  }

  public function update(Request $req, $id) {

    $tarif = Tarif::find($id);

    if (!$tarif) {
      return response()->json([
        'message' => 'Data tarif tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $validated = [
      'id_season' => 'required|numeric', 
      'id_jenis_kamar' => 'required|numeric', 
      'tarif' => 'required|numeric',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    if ($tarif->id_jenis_kamar != $req->id_jenis_kamar || $tarif->id_season != $req->id_season) {

      $checkTarif = Tarif::where('id_jenis_kamar', $req->id_jenis_kamar)->where('id_season', $req->id_season)->first();

      if ($checkTarif) {
        return response()->json([
          'message' => 'Data tarif sudah ada',
          'status' => "error",
          'data' => $validator->errors()
        ], 400);
      }
    
    }

    

    $tarif->id_season = $req->id_season;
    $tarif->id_jenis_kamar = $req->id_jenis_kamar;
    $tarif->tarif = $req->tarif;
    
    $tarif->save();

    return response()->json([
      'message' => 'Data tarif berhasil diupdate',
      'status' => "success",
      'data' => $tarif
    ], 200);
  }
}