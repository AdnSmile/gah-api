<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    $customer->updated_at = null;
    $customer->created_at = Date('Y-m-d H:i:s');

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

  public function loginCustomer(Request $req) {
    
    $validated = [
      'username' => 'required',
      'password' => 'required'
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $customer = Account::where('username', $req->username)->first();

    if (!$customer || !Hash::check($req->password, $customer->password)) {
      return response()->json([
          'success' => "error",
          'message' => 'Username atau password salah',
          'data' => 'Unauthorized',
      ], 401);
    }

    $role = $customer::where('username', $req->username)->value('role');

    $token = $customer->createToken('token', [$role])->plainTextToken;

    return response()->json([
      'message' => 'Login berhasil',
      'status' => "success",
      'data' => [
        'token' => $token,
        'account' => $customer
      ]
    ], 200);
  }

  public function logout(Request $req) {

    $account = Account::where('username', $req->username)->first();

    $account->tokens()->delete();

    return response()->json([
      'message' => 'Logout berhasil',
      'status' => "success",
      'data' => null
    ], 200);
  }

  public function getDetailAccountWithReservasi() {

    $account = Auth::user()->FKAccountInCustomer()->with('FkCustomerInReservasi')->first();

    if (!$account) {
        
        return response()->json([
          'message' => 'Akun tidak ditemukan',
          'status' => "error",
          'data' => null
        ], 404);
    }

    return response()->json([
      'message' => 'Data akun ditemukan',
      'status' => "success",
      'data' => $account
    ], 200);
  }

  public function getDetailAccount() {

    $account = Auth::user();

    return response()->json([
      'message' => 'Data akun ditemukan',
      'status' => "success",
      'data' => $account
    ], 200);
  }

  public function updatePassword(Request $req) {

    $validated = [
      'username' => 'required',
      'password' => 'required',
      'new_password' => 'required'
    ];

    $validator = Validator::make($req->all(), $validated);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Terjadi kesalahan',
        'status' => "error",
        'data' => $validator->errors()
      ], 400);
    }

    $account = Account::where('username', $req->username)->first();

    if (!$account) {

      return response()->json([
        'message' => 'Akun tidak ditemukan',
        'status' => "error",
        'data' => null
      ], 404);
    }

    if (!Hash::check($req->password, $account->password)) {

      return response()->json([
        'message' => 'Password salah',
        'status' => "error",
        'data' => null
      ], 400);
    }

    $account->password = Hash::make($req->new_password);
    $account->save();

    return response()->json([
      'message' => 'Password berhasil diupdate',
      'status' => "success",
      'data' => $account
    ], 200);
  }
}