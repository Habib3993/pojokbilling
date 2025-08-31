<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    /**
     * Menampilkan halaman daftar log aktivitas.
     */
    public function index()
    {
        // Ambil semua log, urutkan dari yang paling baru, dan gunakan pagination
        $activities = Activity::with('causer', 'subject') // Load relasi untuk efisiensi
            ->latest()
            ->paginate(20); 

        return view('logs.index', compact('activities'));
    }
}
