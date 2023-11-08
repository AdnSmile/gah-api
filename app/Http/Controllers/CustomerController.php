<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller {

  public function store(Request $req) {

    $validated = [
      'nama' => 'required',
      'email' => 'required|email|unique:customer,email',
      'alamat' => 'required',
      'institusi' => 'required',
      'no_telpon' => 'required',
      'no_identitas' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);
    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    // $checkCustomer = Customer::where('email', $req->email)->first();

    $customer = Customer::create([
      'nama' => $req->nama,
      'email' => $req->email,
      'alamat' => $req->alamat,
      'institusi' => $req->institusi,
      'no_telpon' => $req->no_telpon,
      'no_identitas' => $req->no_identitas,
    ]);

    if ($customer) {
      return response()->json([
        'message' => 'Customer berhasil ditambahkan',
        'status' => "success",
        'data' => $customer
      ], 200);
    }
  }
  
  public function index() {

    $customer = Customer::all();

    return response()->json([
      'message' => 'Berhasil menampilkan data customer',
      'status' => "success",
      'data' => $customer
    ], 200);
  }

  public function indexGrup() {

    $customer = Customer::whereNotNull('institusi')->get();

    return response()->json([
      'message' => 'Berhasil menampilkan data customer grup',
      'status' => "success",
      'data' => $customer
    ], 200);
  }

  public function show($id) {
    
    $customer = Customer::with('FkCustomerInReservasi')->find($id);

    if (!$customer) {
      return response()->json([
        'message' => 'Data customer tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 400);
    }

    return response()->json([
      'message' => 'Berhasil menampilkan data customer',
      'status' => "success",
      'data' => $customer
    ], 200);
  }

  public function update(Request $req, $id) {

    $customer = Customer::find($id);

    if (!$customer) {
      return response()->json([
        'message' => 'Data customer tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 400);
    }

    $validated = [
      'nama' => 'required',
      'email' => 'required|email|unique:customer,email,'.$id.',id_customer',
      'alamat' => 'required',
      'institusi' => 'required',
      'no_telpon' => 'required',
      'no_identitas' => 'required',
    ];

    $validator = Validator::make($req->all(), $validated);
    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $customer->update([
      'nama' => $req->nama,
      'email' => $req->email,
      'alamat' => $req->alamat,
      'institusi' => $req->institusi,
      'no_telpon' => $req->no_telpon,
      'no_identitas' => $req->no_identitas,
    ]);

    if ($customer) {
      return response()->json([
        'message' => 'Customer berhasil diupdate',
        'status' => "success",
        'data' => $customer
      ], 200);
    }

    return response()->json([
      'message' => 'Customer gagal diupdate',
      'status' => "error",
      'data' => null
    ], 400);
  }
}