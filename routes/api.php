<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JenisKamarController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\ReservasiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AccountController::class, 'registerCustomer']);
Route::post('registerPegawai', [AccountController::class, 'registerPegawai']);


Route::get('jenisKamar', [JenisKamarController::class, 'index']);
Route::post('jenisKamar', [JenisKamarController::class, 'store']);
Route::get('jenisKamar/{idJenisKamar}', [JenisKamarController::class, 'show']);
Route::put('jenisKamar/{idJenisKamar}', [JenisKamarController::class, 'update']);
Route::delete('jenisKamar/{idJenisKamar}', [JenisKamarController::class, 'delete']);

Route::post('login', [AccountController::class, 'loginCustomer']);
Route::post('logout', [AccountController::class, 'logout'])->middleware('auth:sanctum', 'ability:customer,admin,sm,fo,gm,owner');

Route::get('accountDetail', [AccountController::class, 'getDetailAccount'])->middleware('auth:sanctum');

Route::get('detailAccountCustomer', [AccountController::class, 'getDetailAccountWithReservasi'])->middleware('auth:sanctum', 'ability:customer,sm');

Route::put('updatePassword', [AccountController::class, 'updatePassword'])->middleware('auth:sanctum', 'ability:customer,admin,sm,fo,gm,owner');

Route::get('/',function (){
    return response()->json([
        'status' => false,
        'message' => "Unauthorized",
        "data" => "Anda belum login"
    ],401);
})->name('login');


Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
    Route::get('kamar', [KamarController::class, 'index']);
    Route::post('kamar', [KamarController::class, 'store']);
    Route::get('kamar/{idKamar}', [KamarController::class, 'show']);
    Route::put('kamar/{idKamar}', [KamarController::class, 'update']);
    Route::delete('kamar/{idKamar}', [KamarController::class, 'delete']);
});

Route::middleware(['auth:sanctum', 'ability:sm'])->group(function () {
    
    Route::get('fasilitas', [FasilitasController::class, 'index']);
    Route::post('fasilitas', [FasilitasController::class, 'store']);
    Route::get('fasilitas/{id}', [FasilitasController::class, 'show']);
    Route::put('fasilitas/{id}', [FasilitasController::class, 'update']);
    Route::delete('fasilitas/{id}', [FasilitasController::class, 'delete']);

    Route::get('season', [SeasonController::class, 'index']);
    Route::post('season', [SeasonController::class, 'store']);
    Route::get('season/{id}', [SeasonController::class, 'show']);
    Route::put('season/{id}', [SeasonController::class, 'update']);
    Route::delete('season/{id}', [SeasonController::class, 'delete']);

    Route::get('tarif', [TarifController::class, 'index']);
    Route::post('tarif', [TarifController::class, 'store']);
    Route::get('tarif/{id}', [TarifController::class, 'show']);
    Route::put('tarif/{id}', [TarifController::class, 'update']);
    Route::delete('tarif/{id}', [TarifController::class, 'delete']);
});

// customer
Route::post('customer', [CustomerController::class, 'store'])->middleware('auth:sanctum', 'ability:sm');
Route::put('customer/{id}', [CustomerController::class, 'update'])->middleware('auth:sanctum', 'ability:customer,sm');
Route::get('customer', [CustomerController::class, 'index'])->middleware('auth:sanctum', 'ability:sm');
Route::get('customerGrup', [CustomerController::class, 'indexGrup'])->middleware('auth:sanctum', 'ability:sm');
Route::get('customer/{id}', [CustomerController::class, 'show'])->middleware('auth:sanctum', 'ability:customer,sm');

// reservasi
Route::get('reservasi/{id}', [ReservasiController::class, 'show'])->middleware('auth:sanctum', 'ability:customer,sm');

// riwayat transaksi
Route::get('riwayatTransaksi', [AccountController::class, 'getRiwayatTransaksiCustomer'])->middleware('auth:sanctum', 'ability:customer,sm');

Route::get('detailReservasi/{id}', [ReservasiController::class, 'show'])->middleware('auth:sanctum', 'ability:customer,sm');