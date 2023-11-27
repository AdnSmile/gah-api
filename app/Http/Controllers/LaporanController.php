<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Reservasi;
use App\Http\Controllers\Carbon;
use App\Models\TransaksiKamar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    public function laporanCustomerBaru($year) {
        $customer = new Customer();
        $jumlahCustomer = $customer->getCustomerCountByMonth($year);

        return response()->json([
            'message' => 'Berhasil menampilkan data customer baru',
            'status' => "success",
            'data' => $jumlahCustomer
        ], 200);
    }

    public function laporanPendapatan($year) {
        $invoice = new Invoice();
        $pendapatan = $invoice->getInvoiceTotalsByMonth($year);

        return response()->json([
            'message' => 'Berhasil menampilkan data pendapatan bulanan',
            'status' => "success",
            'data' => $pendapatan
        ], 200);
    }

    public function laporanJumlahCustomer($year, $month) {
        $kamar = new TransaksiKamar();
        $jumlahCustomer = $kamar->getRoomStatisticsForMonth($year, $month);

        return response()->json([
            'message' => 'Berhasil menampilkan data jumlah customer',
            'status' => "success",
            'data' => $jumlahCustomer
        ], 200);
    }

    public function laporanPemesananTerbanyak() {
        
        $reservasi = new Reservasi();
        $pemesananTerbanyak = $reservasi->getTopCustomers();

        return response()->json([
            'message' => 'Berhasil menampilkan data pemesanan terbanyak',
            'status' => "success",
            'data' => $pemesananTerbanyak
        ], 200);
    }
}