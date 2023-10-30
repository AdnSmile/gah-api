<?php

use App\Http\Controllers\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JenisKamarController;
use App\Http\Controllers\KamarController;

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


Route::get('kamar', [KamarController::class, 'index']);
Route::post('kamar', [KamarController::class, 'store']);
Route::get('kamar/{idKamar}', [KamarController::class, 'show']);
Route::put('kamar/{idKamar}', [KamarController::class, 'update']);
Route::delete('kamar/{idKamar}', [KamarController::class, 'delete']);