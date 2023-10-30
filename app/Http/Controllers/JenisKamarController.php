<?php

namespace App\Http\Controllers;

use App\Models\JenisKamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JenisKamarController extends Controller {

  public function index() {

    $jenisKamar = JenisKamar::all();

    return response()->json([
      'message' => 'Daftar data jenis kamar',
      'status' => "success",
      'data' => $jenisKamar
    ], 200);
  }

  public function store(Request $req) {

    $jenisKamar = new JenisKamar();

    $validated = [
      'nama' => 'required',
      'tipe_bed' => 'required',
      'tarif_dasar' => 'required|numeric',
      'rincian_kamar' => 'required',
      'deskripsi' => 'required',
      'kapasitas' => 'required|numeric',
      'ukuran_kamar' => 'required|numeric',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $jenisKamar->nama = $req->nama;
    $jenisKamar->tipe_bed = $req->tipe_bed;
    $jenisKamar->tarif_dasar = $req->tarif_dasar;
    $jenisKamar->rincian_kamar = $req->rincian_kamar;
    $jenisKamar->deskripsi = $req->deskripsi;
    $jenisKamar->kapasitas = $req->kapasitas;
    $jenisKamar->ukuran_kamar = $req->ukuran_kamar;

    $jenisKamar->save();

    return response()->json([
      'message' => 'Jenis kamar berhasil ditambahkan',
      'status' => "success",
      'data' => $jenisKamar
    ], 200);
  }

  public function update(Request $req, $id) {

    $jenisKamar = JenisKamar::find($id);

    if (!$jenisKamar) {
      return response()->json([
        'message' => 'Data jenis kamar tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $validated = [
      'nama' => 'required',
      'tipe_bed' => 'required',
      'tarif_dasar' => 'required|numeric',
      'rincian_kamar' => 'required',
      'deskripsi' => 'required',
      'kapasitas' => 'required|numeric',
      'ukuran_kamar' => 'required|numeric',
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $jenisKamar->nama = $req->nama;
    $jenisKamar->tipe_bed = $req->tipe_bed;
    $jenisKamar->tarif_dasar = $req->tarif_dasar;
    $jenisKamar->rincian_kamar = $req->rincian_kamar;
    $jenisKamar->deskripsi = $req->deskripsi;
    $jenisKamar->kapasitas = $req->kapasitas;
    $jenisKamar->ukuran_kamar = $req->ukuran_kamar;

    $jenisKamar->save();

    return response()->json([
      'message' => 'Jenis kamar berhasil diupdate',
      'status' => "success",
      'data' => $jenisKamar
    ], 200);
  }

  public function show($id) {

    $jenisKamar = JenisKamar::find($id);

    if (!$jenisKamar) {
      return response()->json([
        'message' => 'Data jenis kamar tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    return response()->json([
      'message' => 'Detail data jenis kamar',
      'status' => "success",
      'data' => $jenisKamar
    ], 200);
  }

  public function delete($id) {

    $jenisKamar = JenisKamar::find($id);

    if (!$jenisKamar) {
      return response()->json([
        'message' => 'Data jenis kamar tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    $jenisKamar->delete();

    return response()->json([
      'message' => 'Data jenis kamar berhasil dihapus',
      'status' => "success",
      'data' => $jenisKamar
    ], 200);
  }
}