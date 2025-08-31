<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Asumsi: Model untuk data pelanggan
use App\Models\Transaction; // Asumsi: Model untuk data transaksi/pendapatan
use App\Models\Expense; // Asumsi: Model untuk data pengeluaran
// Ganti nama model di atas jika berbeda dengan proyek Anda.

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menyediakan data statistik untuk dashboard.
     */
    public function getStats(Request $request)
    {
        $today = Carbon::now();
        $startOfMonth = $today->copy()->startOfMonth();
        $startOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $startOfLastMonth->copy()->endOfMonth();

        // =================================================================
        // 1: USER (ACTIVE USERS)
        // Logika: Jumlah total semua pelanggan.
        // =================================================================
        $activeUsersCount = User::count();
        $targetPelanggan = 500; // Target bisa diubah atau diambil dari database
        $activeUsersPercentage = ($targetPelanggan > 0) ? round(($activeUsersCount / $targetPelanggan) * 100) : 0;
        $activeUsers = [
            'count' => number_format($activeUsersCount),
            'percentage' => 0, // Tidak ada perbandingan periode
            'series' => [$activeUsersPercentage]
        ];

        // =================================================================
        // 2: PENDAPATAN (REVENUE)
        // Logika: Gross income pada bulan berjalan.
        // =================================================================
        $revenueThisMonth = Transaction::whereBetween('created_at', [$startOfMonth, $today])->sum('amount');
        $revenueLastMonth = Transaction::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('amount');
        $revenuePercentageChange = ($revenueLastMonth > 0) ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100) : 0;

        $revenueSeries = Transaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('created_at', '>=', $startOfMonth)
            ->groupBy('date')->orderBy('date', 'ASC')->pluck('total')->toArray();
        $revenue = [
            'total' => 'Rp ' . number_format($revenueThisMonth),
            'percentage' => $revenuePercentageChange,
            'series' => $revenueSeries
        ];

        // =================================================================
        // 3: PASANG SAMBUNG BARU (PSB)
        // Logika: Jumlah pelanggan baru pada bulan berjalan.
        // =================================================================
        $psbThisMonthCount = User::whereBetween('created_at', [$startOfMonth, $today])->count();
        $psbLastMonthCount = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $psbPercentageChange = ($psbLastMonthCount > 0) ? round((($psbThisMonthCount - $psbLastMonthCount) / $psbLastMonthCount) * 100) : 0;

        $psbSeries = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(id) as total'))
            ->where('created_at', '>=', $startOfMonth)
            ->groupBy('date')->orderBy('date', 'ASC')->pluck('total')->toArray();
        $subscriptions = [
            'count' => number_format($psbThisMonthCount),
            'percentage' => $psbPercentageChange,
            'series' => $psbSeries
        ];

        // =================================================================
        // 4: EKSPANSI (EXPENSES)
        // Logika: Total pengeluaran pada bulan berjalan.
        // =================================================================
        $expensesThisMonth = Expense::whereBetween('created_at', [$startOfMonth, $today])->sum('amount');
        $expensesLastMonth = Expense::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('amount');
        $expensesPercentageChange = ($expensesLastMonth > 0) ? round((($expensesThisMonth - $expensesLastMonth) / $expensesLastMonth) * 100) : 0;

        $expensesSeries = Expense::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('created_at', '>=', $startOfMonth)
            ->groupBy('date')->orderBy('date', 'ASC')->pluck('total')->toArray();
        $expenses = [
            'total' => 'Rp ' . number_format($expensesThisMonth),
            'percentage' => $expensesPercentageChange,
            'series' => $expensesSeries
        ];

        // =================================================================
        // 5: SALES (GROWTH)
        // Logika: Jumlah pelanggan baru dari sales bulan ini.
        // *** PERUBAHAN LOGIKA DI SINI ***
        // =================================================================
        $growthThisMonth = $psbThisMonthCount; // Menggunakan data jumlah PSB
        $growthLastMonth = $psbLastMonthCount;
        $growthPercentage = ($growthLastMonth > 0) ? round((($growthThisMonth - $growthLastMonth) / $growthLastMonth) * 100) : 0;
        $growth = [
            'total' => number_format($growthThisMonth), // Menampilkan JUMLAH pelanggan baru
            'percentage' => $growthPercentage, // Persentase perubahan dari bulan lalu
            'series' => [$growthPercentage > 0 ? $growthPercentage : 0] // Grafik menampilkan persentase pertumbuhan
        ];

        // =================================================================
        // 6: BEBAS (CUSTOM METRIC)
        // Logika: Dibiarkan sebagai contoh statis.
        // =================================================================
        $customMetric = [
            'count' => number_format(15),
            'percentage' => 5,
            'series' => [60, 30, 10],
            'labels' => ['Baru', 'Proses', 'Selesai']
        ];

        // =================================================================
        // FINAL RESPONSE
        // =================================================================
        return response()->json([
            'activeUsers'   => $activeUsers,
            'revenue'       => $revenue,
            'subscriptions' => $subscriptions,
            'expenses'      => $expenses,
            'growth'        => $growth,
            'customMetric'  => $customMetric,
        ]);
    }
}
