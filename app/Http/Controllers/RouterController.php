<?php

namespace App\Http\Controllers;

use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// Tambahkan library untuk koneksi ke MikroTik
use RouterOS\Client;
use RouterOS\Query;
use Exception; // Tambahkan Exception untuk menangani error

class RouterController extends Controller
{
    /**
     * Menampilkan daftar semua router.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $routers = Router::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('ip_address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
            
        return view('routers.index', compact('routers'));
    }

    /**
     * Menampilkan form untuk membuat router baru.
     */
    public function create()
    {
        return view('routers.create');
    }

    /**
     * Menyimpan router baru setelah melakukan tes koneksi (DENGAN DEBUGGING).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'port' => 'required|integer',
        ]);

        try {
            $client = new Client([
                'host' => $validated['ip_address'],
                'user' => $validated['username'],
                'pass' => $validated['password'],
                'port' => (int)$validated['port'],
                'timeout' => 300,
            ]);
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal terhubung ke router: ' . $e->getMessage());
        }

        // Simpan ke database jika koneksi sukses
        Router::create($validated);

        return redirect()->route('routers.index')
        ->with('success', 'berhasil terhubung ke router');
    }


    /**
     * Menampilkan form untuk mengedit data router.
     */
    public function edit(Router $router)
    {
        return view('routers.edit', compact('router'));
    }

    /**
     * Memperbarui data router setelah melakukan tes koneksi.
     */
    public function update(Request $request, Router $router)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'port' => 'required|integer',
        ]);

        $password = $request->filled('password') ? $validated['password'] : $router->password;

        // --- LANGKAH 1: TES KONEKSI DENGAN DATA BARU ---
        try {
            $client = new Client([
                'host' => $validated['ip_address'],
                'user' => $validated['username'],
                'pass' => $password,
                'port' => (int)$validated['port'],
                'timeout' => 300,
            ]);

        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal terhubung dengan data router yang baru: ' . $e->getMessage());
        }

        // --- LANGKAH 2: JIKA KONEKSI BERHASIL, PERBARUI DATABASE ---
        $updateData = $request->except('password');
        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        $router->update($updateData);

        return redirect()->route('routers.index')->with('success', 'Router berhasil diperbarui dan koneksi terverifikasi.');
    }

    /**
     * Menghapus data router dari database.
     */
    public function destroy(Router $router)
    {
        // Validasi: jangan hapus router jika masih digunakan
        if ($router->ipPools()->count() > 0) {
            return back()->with('error', 'Router tidak bisa dihapus karena masih terhubung dengan IP Pool.');
        }
        
        // Tambahkan validasi lain jika perlu (misal: terhubung dengan paket, dll)

        $router->delete();
        return redirect()->route('routers.index')->with('success', '');
    }
    public function getStatus(Router $router)
    {
        try {
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int)$router->port,
                'timeout' => 5,
            ]);

            // Mengambil data resource (CPU & Uptime)
            $response = $client->query('/system/resource/print')->read();
            
            $statusData = [
                'uptime' => $response[0]['uptime'],
                'cpu_load' => $response[0]['cpu-load'] . '%',
            ];

            // Kirim data sebagai JSON jika berhasil
            return response()->json([
                'success' => true,
                'data' => $statusData
            ]);

        } catch (\Exception $e) {
            // Log error sebenarnya ke file laravel.log
            Log::error('MikroTik Connection Failed: ' . $e->getMessage());
    
            // Kirim pesan error generik sebagai JSON
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke router. Periksa log untuk detail.'
            ], 500);
        }
    }
}
