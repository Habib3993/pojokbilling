<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;    // <-- MENGGUNAKAN MODEL YANG BENAR
use App\Models\Payment;     // <-- MENGGUNAKAN MODEL YANG BENAR
use App\Models\User; // Asumsi: Model untuk data pelanggan
use App\Models\Transaction; // Asumsi: Model untuk data transaksi/pendapatan
use App\Models\Expense; // Asumsi: Model untuk data pengeluaran
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Menyediakan data statistik untuk dashboard.
     */
    public function getStats(Request $request)
    {
        $user = auth()->user();
        
        // Jika tidak ada user yang login, kirim respons error yang jelas
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // =================================================================
        // 1: HANYA FOKUS PADA TOTAL PELANGGAN
        // =================================================================
        $customerQuery = \App\Models\Customer::query();

        // Logika untuk membedakan superadmin dan admin biasa
        if (!$user->hasRole('superadmin')) {
            $customerQuery->where('location_id', $user->location_id);
        }

        $totalCustomers = $customerQuery->count();
        $targetPelanggan = 1000;
        $percentageToTarget = ($targetPelanggan > 0) ? round(($totalCustomers / $targetPelanggan) * 100) : 0;

        $activeUsers = [
            'count' => number_format($totalCustomers),
            'percentage' => 0, // Placeholder
            'series' => [$percentageToTarget]
        ];

        // =================================================================
        // 2: REVENUE (Revisi Logika untuk Chart Bulanan)
        // =================================================================
        
        // Query dasar yang akan digunakan berulang kali
        $baseRevenueQuery = Transaction::query();
        if (!$user->hasRole('superadmin')) {
            $baseRevenueQuery->where('location_id', $user->location_id);
        }

        $revenueLabels = [];
        $revenueSeries = [];

        // Loop untuk 3 bulan terakhir (termasuk bulan ini) untuk mendapatkan data per bulan
        for ($i = 3; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y'); // Format: Jul 2025

            // Kloning query agar tidak menimpa
            $queryForMonth = clone $baseRevenueQuery;

            $monthlyRevenue = $queryForMonth
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('debit');

            $revenueLabels[] = $monthName;
            $revenueSeries[] = $monthlyRevenue;
        }

        // Hitung total pendapatan dari 3 bulan untuk ditampilkan di atas chart
        $totalRevenue3Months = array_sum($revenueSeries);

        $revenue = [
            'total' => 'Rp ' . number_format($totalRevenue3Months),
            'labels' => $revenueLabels,  // Kirim label bulan (e.g., ["Jul 2025", "Aug 2025"])
            'series' => $revenueSeries   // Kirim data pendapatan per bulan (e.g., [50000, 75000])
        ];

        // =================================================================
        // 3: SALES PERFORMANCE
        // =================================================================
        $salesData = (clone $customerQuery) // Clone dari query customer awal
            ->select('sales', DB::raw('count(*) as total_pelanggan'))
            ->whereNotNull('sales')
            ->where('sales', '!=', '')
            ->groupBy('sales')
            ->orderByRaw('COUNT(*) DESC')
            ->get();
            
        $totalSalesPersons = $salesData->count(); // 1. Hitung jumlah sales dari data yang didapat

        $salesPerformance = [
            'count'  => number_format($totalSalesPersons), // 2. Gunakan jumlah sales, bukan total pelanggan
            'labels' => $salesData->pluck('sales')->toArray(),
            'series' => $salesData->pluck('total_pelanggan')->toArray(),
        ];
        
        // =================================================================
        // 4: EXPENSES (EKSPANSI) - 4 BULAN TERAKHIR (KODE BARU)
        // =================================================================
        
        // Query dasar untuk pengeluaran ekspansi, termasuk filter lokasi untuk admin biasa
        $baseExpenseQuery = Transaction::query()->where('status', 'EKSPANSI');
        if (!$user->hasRole('superadmin')) {
            $baseExpenseQuery->where('location_id', $user->location_id);
        }

        $expensesLabels = [];
        $expensesSeries = [];

        // Loop untuk 4 bulan terakhir (termasuk bulan ini)
        for ($i = 3; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');

            $queryForMonth = clone $baseRevenueQuery;

            $monthlyExpense = $queryForMonth
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->where('status', 'EKSPANSI')
                ->sum('kredit'); // Menjumlahkan kolom 'kredit' untuk pengeluaran

            $expensesLabels[] = $monthName;
            $expensesSeries[] = $monthlyExpense;
        }

        // Calculate percentage for expenses
        $lastMonthExpense = $expensesSeries[count($expensesSeries) - 2] ?? 0; // Previous month
        $currentMonthExpense = $expensesSeries[count($expensesSeries) - 1] ?? 0; // Current month
        $expensePercentage = $lastMonthExpense > 0 ? round((($currentMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100) : 0;
        $expenses['percentage'] = $expensePercentage;

        $expenses = [
            'labels' => $expensesLabels,
            'series' => $expensesSeries
        ];

        // =================================================================
        // 5: GROWTH (PSB) - 4 BULAN TERAKHIR (KODE BARU)
        // =================================================================
        
        // Query dasar untuk customer, termasuk filter lokasi
        $baseGrowthQuery = Customer::query();
        if (!$user->hasRole('superadmin')) {
            $baseGrowthQuery->where('location_id', $user->location_id);
        }

        $growthSeries = [];
        $growthLabels = [];

        // Loop untuk 4 bulan terakhir
        for ($i = 3; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $growthLabels[] = $date->format('M Y'); // Label: Sep 2025

            // Kloning query agar tidak menimpa
            $queryForMonth = clone $baseGrowthQuery;

            $monthlyPsb = $queryForMonth
                ->whereYear('subscription_date', $date->year)
                ->whereMonth('subscription_date', $date->month)
                ->count(); // Hitung jumlah customer baru di bulan tersebut

            $growthSeries[] = $monthlyPsb;
        }

        // Calculate percentage for growth
        $lastMonthGrowth = $growthSeries[count($growthSeries) - 2] ?? 0; // Previous month
        $currentMonthGrowth = $growthSeries[count($growthSeries) - 1] ?? 0; // Current month
        $growthPercentage = $lastMonthGrowth > 0 ? round((($currentMonthGrowth - $lastMonthGrowth) / $lastMonthGrowth) * 100) : 0;
        $growth['percentage'] = $growthPercentage;

        // Ambil data PSB bulan ini untuk ditampilkan sebagai angka utama
        $currentMonthPsb = end($growthSeries);

        // Ensure growth data is valid
        if (empty($growthSeries)) {
            $growthSeries = [0]; // Default to zero if no data is available
        }
        if (empty($growthLabels)) {
            $growthLabels = ['No Data']; // Default label if no data is available
        }

        $growth = [
            'total'      => number_format($currentMonthPsb),
            'percentage' => $growthPercentage,
            'series'     => $growthSeries,
            'labels'     => $growthLabels
        ];

        // =================================================================
        // KIRIM SEMUA DATA KE FRONTEND
        // =================================================================
        return response()->json([
            'totalUsers'    => $activeUsers,
            'revenue'         => $revenue,
            'sales'         => $salesPerformance,
            'expenses'     => $expenses,
            'growth'       => $growth,
            // Kita buat data dummy untuk card lain agar tidak error di frontend
            'customMetric'  => ['count' => 0, 'percentage' => 0, 'series' => []],
        ]);
    }
}
