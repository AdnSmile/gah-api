<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeasonController extends Controller { 

  public function index() {
      
      $season = Season::all();
  
      return response()->json([
        'message' => 'Daftar data season',
        'status' => "success",
        'data' => $season
      ], 200);
  }

  public function store(Request $req) {

    $validated = [
      'nama' => 'required',
      'jenis' => 'required',
      'start_season' => 'required',
      'end_season' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $checkSeason = Season::where('nama', $req->nama)->first();

    if ($checkSeason) {
      return response()->json([
        'message' => 'Data season sudah ada',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    if($req->start_season < Carbon::now() -> addMonths(2)) {
      return response()->json([
        'message' => 'Start season minimal 2 bulan dari sekarang',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $season = Season::create([
      'nama' => $req->nama,
      'jenis' => $req->jenis,
      'start_season' => $req->start_season,
      'end_season' => $req->end_season,
    ]);

    if ($season) {
      return response()->json([
        'message' => 'Season berhasil ditambahkan',
        'status' => "success",
        'data' => $season
      ], 200);
    }
  }

  public function show($id) {

    $season = Season::with("FkSeasonInTarif.FkTarifInSeason")->find($id);

    if (!$season) {
      return response()->json([
        'message' => 'Data season tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    return response()->json([
      'message' => 'Data season',
      'status' => "success",
      'data' => $season
    ], 200);
  }

  public function update(Request $req, $id) {

    $season = Season::find($id);

    if (!$season) {
      return response()->json([
        'message' => 'Data season tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $validated = [
      'nama' => 'required',
      'jenis' => 'required',
      'start_season' => 'required',
      'end_season' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    if($req->start_season < Carbon::now() -> addMonths(2)) {
      return response()->json([
        'message' => 'Start season minimal 2 bulan dari sekarang',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    if ($season->nama != $req->nama) {

      $checkSeason = Season::where('nama', $req->nama)->first();

    if ($checkSeason) {
      return response()->json([
        'message' => 'Data season sudah ada',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }
    }

    $season->nama = $req->nama;
    $season->jenis = $req->jenis;
    $season->start_season = $req->start_season;
    $season->end_season = $req->end_season;

    $season->save();

    return response()->json([
      'message' => 'Data season berhasil diubah',
      'data' => $season
    ], 200);
  }

  public function delete($id) {

    $season = Season::find($id);

    if (!$season) {
      return response()->json([
        'message' => 'Data season tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    if ($season->start_season < Carbon::now() -> addMonths(2)) {

      return response()->json([
        'message' => 'Start season minimal 2 bulan dari sekarang',
        'status' => "error",
        'data' => null
      ], 400);
    }

    $season->delete();

    return response()->json([
      'message' => 'Data season berhasil dihapus',
      'status' => "success",
      'data' => $season
    ], 200);
  }
}