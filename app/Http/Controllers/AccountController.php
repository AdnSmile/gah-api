<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller {

  public function registerCustomer(Request $req) {

    $validated = [
      'username' => 'required',
      'password' => 'required',
      'nama' => 'required',
      'email' => 'required',
      'no_telpon' => 'required',
      'alamat' => 'required',
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

    $customer = new Customer();
    $customer->nama = $req->nama;
    $customer->email = $req->email;
    $customer->no_telpon = $req->no_telpon;
    $customer->alamat = $req->alamat;
    $customer->no_identitas = $req->no_identitas;
    $customer->institusi = $req->institusi;

    $customer->save();

    $account = new Account();
    $account->id_customer = $customer->id_customer;
    $account->username = $req->username;  
    $account->password = Hash::make($req->password);
    $account->nama = $req->nama;
    $account->email = $req->email;
    $account->role = 'customer';

    $account->save();

    return response()->json([
      'message' => 'Akun berhasil dibuat',
      'status' => "success",
      'data' => $account
    ], 200);
  }

  public function registerPegawai(Request $req) {

    $validated = [
      'username' => 'required',
      'password' => 'required',
      'nama' => 'required',
      'email' => 'required',
      'role' => 'required'
    ];

    $validator = Validator::make($req->all(), $validated);
    
    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $account = new Account();
    $account->username = $req->username;  
    $account->password = Hash::make($req->password);
    $account->nama = $req->nama;
    $account->email = $req->email;
    $account->role = $req->role;

    $account->save();

    return response()->json([
      'message' => 'Akun berhasil dibuat',
      'status' => "success",
      'data' => $account
    ], 200);
  }
}