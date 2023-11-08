<?php

namespace App\Http\Controllers;

use App\Models\JenisKamar;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KamarController extends Controller {

  public function index() {

    $kamar = Kamar::with("FKKamarInJenisKamar")->get();

    return response()->json([
      'message' => 'Daftar data jenis kamar',
      'status' => "success",
      'data' => $kamar
    ], 200);
  }

  public function store(Request $req) {

    $validated = [
      'id_jenis_kamar' => 'required',
      'id_kamar' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $checkKamar = Kamar::where('id_kamar', $req->id_kamar)->first();
    if ($checkKamar) {
      return response()->json([
        'message' => 'Data kamar sudah ada',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $kamar = Kamar::create([
      'id_jenis_kamar' => $req->id_jenis_kamar,
      'id_kamar' => $req->id_kamar,
    ]);

    if ($kamar) {
      return response()->json([
        'message' => 'Jenis kamar berhasil ditambahkan',
        'status' => "success",
        'data' => $kamar
      ], 200);
    }
  }

  public function show($idKamar) {

    $kamar = Kamar::find($idKamar);

    if (!$kamar) {
      return response()->json([
        'message' => 'Data kamar tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $kamar = Kamar::with("FKKamarInJenisKamar")->find($idKamar);
    $jenisKamar = JenisKamar::where('id_jenis_kamar', $kamar->id_jenis_kamar)->first();

    if ($kamar) {
      return response()->json([
        'message' => 'Detail data jenis kamar',
        'status' => "success",
        'data' => $kamar
      ], 200);
    } else {
      return response()->json([
        'message' => 'Data jenis kamar tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }
  }

  public function update(Request $req, $idKamar) {

    $kamar = Kamar::find($idKamar);

    if (!$kamar) {
      return response()->json([
        'message' => 'Data jenis kamar tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $validated = [
      'id_jenis_kamar' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    if ($kamar->id_kamar != $req->id_kamar) {

      $checkKamar = Kamar::where('id_kamar', $req->id_kamar)->first();
      if ($checkKamar) {
        return response()->json([
          'message' => 'Data kamar sudah ada',
          'status' => "error",
          'data' => $validator->errors()
        ], 400);
      }
    }

    $kamar->id_jenis_kamar = $req->id_jenis_kamar;

    $kamar->save();

    return response()->json([
      'message' => 'Data jenis kamar berhasil diubah',
      'status' => "success",
      'data' => $kamar
    ], 200);
  }

  public function delete($idKamar) {

    $kamar = Kamar::find($idKamar);

    if (!$kamar) {
      return response()->json([
        'message' => 'Data jenis kamar tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $kamar->delete();

    return response()->json([
      'message' => 'Data jenis kamar berhasil dihapus',
      'status' => "success",
      'data' => $kamar
    ], 200);
  }
}