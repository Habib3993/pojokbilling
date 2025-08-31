<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    // app/Http/Controllers/ReportController.php

    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
    
        // Transaksi bulan berjalan
        $transactions = Transaction::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
    
        // Cari bulan sebelumnya
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
    
        // Ambil semua transaksi sampai akhir bulan sebelumnya
        $transactionsBefore = Transaction::whereDate('date', '<=', $prevMonth->copy()->endOfMonth())
            ->get();
    
        // Hitung saldo bulan lalu
        $saldoBulanLalu = $transactionsBefore->sum('debit') - $transactionsBefore->sum('kredit');
    
        // --- Perhitungan bulan berjalan ---
        $grossIncome = $transactions->sum('debit');
        $operationalExpense = $transactions->where('status', 'OPERASIONAL')->sum('kredit');
        $ekspansiExpense = $transactions->where('status', 'EKSPANSI')->sum('kredit');
        $totalExpense = $operationalExpense + $ekspansiExpense;
    
        $netIncome = $grossIncome - $totalExpense;
    
        // Total debit = saldo awal + pemasukan bulan ini
        $totalDebit = $saldoBulanLalu + $grossIncome;
    
        $totalKreditOperasional = $operationalExpense;
        $totalKreditEkspansi = $ekspansiExpense;
    
        // Saldo akhir = saldo bulan lalu + pemasukan bulan ini - pengeluaran bulan ini
        $saldo = $saldoBulanLalu + $netIncome;
    
        return view('reports.index', compact(
            'month', 'year', 'grossIncome', 'operationalExpense', 'netIncome',
            'totalDebit', 'totalKreditOperasional', 'totalKreditEkspansi', 'saldo',
            'saldoBulanLalu'
        ));
    }
    
}
