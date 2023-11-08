<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FasilitasController extends Controller {

  public function index() {

    $fasilitas = Fasilitas::all();

    return response()->json([
      'message' => 'Daftar data Fasilitas',
      'status' => "success",
      'data' => $fasilitas
    ], 200);
  }

  public function store(Request $req) {
    $validated = [
      'nama_layanan' => 'required',
      'tarif_layanan' => 'required|numeric',
      'satuan' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);
    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $checkFasilitas = Fasilitas::where('nama_layanan', $req->nama_layanan)->first();

    if ($checkFasilitas) {
      return response()->json([
        'message' => 'Data fasilitas sudah ada',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $fasilitas = Fasilitas::create([
      'nama_layanan' => $req->nama_layanan,
      'tarif_layanan' => $req->tarif_layanan,
      'satuan' => $req->satuan,
    ]);

    if ($fasilitas) {
      return response()->json([
        'message' => 'Fasilitas berhasil ditambahkan',
        'status' => "success",
        'data' => $fasilitas
      ], 200);
    }
  }

  public function show($id) {

    $fasilitas = Fasilitas::find($id);

    if (!$fasilitas) {
      return response()->json([
        'message' => 'Data fasilitas tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    return response()->json([
      'message' => 'Data fasilitas ditemukan',
      'status' => "success",
      'data' => $fasilitas
    ], 200);
  }

  public function update(Request $req, $id) {

    $fasilitas = Fasilitas::find($id);

    if (!$fasilitas) {
      return response()->json([
        'message' => 'Data fasilitas tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $validated = [
      'nama_layanan' => 'required',
      'tarif_layanan' => 'required|numeric',
      'satuan' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);
    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    if ($fasilitas->nama_layanan != $req->nama_layanan) {

      $checkFasilitas = Fasilitas::where('nama_layanan', $req->nama_layanan)->first();

    if ($checkFasilitas) {
      return response()->json([
        'message' => 'Data fasilitas sudah ada',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }
    }

    $fasilitas->nama_layanan = $req->nama_layanan;
    $fasilitas->tarif_layanan = $req->tarif_layanan;
    $fasilitas->satuan = $req->satuan;

    $fasilitas->save();

    return response()->json([
      'message' => 'Data fasilitas berhasil diupdate',
      'status' => "success",
      'data' => $fasilitas
    ], 200);
  }

  public function delete($id) {

    $fasilitas = Fasilitas::find($id);

    if (!$fasilitas) {
      return response()->json([
        'message' => 'Data fasilitas tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $fasilitas->delete();

    return response()->json([
      'message' => 'Data fasilitas berhasil dihapus',
      'status' => "success",
      'data' => $fasilitas
    ], 200);
  }
}