<?php

namespace App\Http\Controllers;

use App\Models\IpPool;
use App\Models\Router;
use Illuminate\Http\Request;
use RouterOS\Client;
use RouterOS\Query;

class IpPoolController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $ipPools = IpPool::with('router')
            ->when($search, function ($query, $search) {
                return $query->where('pool_name', 'like', "%{$search}%")
                             ->orWhere('ranges', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
        $routers = Router::all(); // ⬅️ tambahkan ini
            
        return view('ip_pools.index', compact('ipPools', 'routers'));
    }

    public function create()
    {
        $routers = Router::all();
        return view('ip_pools.create', compact('routers'));
    }

    /**
     * FUNGSI INI DIMODIFIKASI DENGAN LOGIKA YANG BENAR
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pool_name' => 'required|string|max:255',
            'ranges' => 'required|string|max:255',
            'router_id' => 'required|exists:routers,id',
        ]);

        $router = Router::findOrFail($validated['router_id']);

        // --- LANGKAH 1: COBA BUAT IP POOL DI MIKROTIK DULU ---
        try {
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int)$router->port, // Pastikan port adalah integer
            ]);
            
            $query = (new Query('/ip/pool/add'))
                ->equal('name', $validated['pool_name'])
                ->equal('ranges', $validated['ranges']);

            // Kirim query. Jika gagal, akan melempar Exception.
            $client->query($query)->read();

        } catch (\Exception $e) {
            // JIKA GAGAL, hentikan semua proses dan tampilkan error.
            // Data TIDAK akan tersimpan di database lokal.
            return back()->withInput()->with('error', 'Gagal membuat IP Pool di MikroTik: ' . $e->getMessage());
        }
        
        // --- LANGKAH 2: JIKA MIKROTIK BERHASIL, BARU SIMPAN KE DATABASE LOKAL ---
        IpPool::create($validated);

        return redirect()->route('ip-pools.index')->with('success', 'IP Pool berhasil ditambahkan di aplikasi dan MikroTik.');
    }

    public function edit(IpPool $ipPool)
    {
        $routers = Router::all();
        return view('ip_pools.edit', compact('ipPool', 'routers'));
    }

    public function update(Request $request, IpPool $ipPool)
    {
        $validated = $request->validate([
            'pool_name' => 'required|string|max:255',
            'ranges' => 'required|string|max:255',
            'router_id' => 'required|exists:routers,id',
        ]);

        $router = $ipPool->router;

        try {
            if ($router) {
                $client = new Client([
                    'host' => $router->ip_address,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => (int)$router->port,
                ]);

                $query = (new Query('/ip/pool/print'))
                    ->where('name', $ipPool->pool_name);
                
                $response = $client->query($query)->read();

                if (!empty($response)) {
                    $poolId = $response[0]['.id'];
                    $updateQuery = (new Query('/ip/pool/set'))
                        ->equal('.id', $poolId)
                        ->equal('name', $validated['pool_name'])
                        ->equal('ranges', $validated['ranges']);
                    
                    $client->query($updateQuery)->read();
                } else {
                    // Jika pool tidak ditemukan di Mikrotik, mungkin lebih baik membuatnya
                    $addQuery = (new Query('/ip/pool/add'))
                        ->equal('name', $validated['pool_name'])
                        ->equal('ranges', $validated['ranges']);
                    $client->query($addQuery)->read();
                }
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui IP Pool di MikroTik: ' . $e->getMessage());
        }

        $ipPool->update($validated);

        return redirect()->route('ip-pools.index')->with('success', 'IP Pool berhasil diperbarui di aplikasi dan MikroTik.');
    }

    public function destroy(IpPool $ipPool)
    {
        try {
            if ($ipPool->packages()->count() > 0) {
                $usedByPackage = $ipPool->packages()->first()->name;
                return back()->with('error', "Tidak bisa menghapus IP Pool '{$ipPool->pool_name}' karena masih digunakan oleh Paket '{$usedByPackage}'.");
            }

            $router = $ipPool->router;

            if ($router) {
                $client = new Client([
                    'host' => $router->ip_address,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => (int)$router->port,
                ]);

                $query = (new Query('/ip/pool/print'))
                    ->where('name', $ipPool->pool_name);
                
                $response = $client->query($query)->read();

                if (!empty($response)) {
                    $poolId = $response[0]['.id'];
                    $removeQuery = (new Query('/ip/pool/remove'))
                        ->equal('.id', $poolId);
                    
                    $client->query($removeQuery)->read();
                }
            }
            
            $ipPool->delete();

            return redirect()->route('ip-pools.index')->with('success', 'IP Pool berhasil dihapus dari aplikasi dan MikroTik.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus IP Pool dari MikroTik: ' . $e->getMessage());
        }
    }
    // di app/Http/Controllers/IpPoolController.php

    public function sync($routerId)
    {
        $router = Router::findOrFail($routerId);

        try {
            // Langkah 1: Terhubung ke router MikroTik
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int)$router->port,
            ]);

            // Langkah 2: Ambil semua IP Pool dari MikroTik
            $mikrotikPools = $client->query('/ip/pool/print')->read();

            // Langkah 3: Ambil nama pool yang sudah ada di database LOKAL untuk router ini
            $existingPoolNames = IpPool::where('router_id', $router->id)
                                    ->pluck('pool_name')
                                    ->toArray();

            $poolsImportedCount = 0;

            // Langkah 4: Loop setiap pool dari MikroTik dan simpan jika belum ada
            foreach ($mikrotikPools as $pool) {
                // Cek apakah nama pool dari MikroTik belum ada di database lokal
                if (!in_array($pool['name'], $existingPoolNames)) {
                    // Jika belum ada, simpan ke database
                    IpPool::create([
                        'router_id' => $router->id,
                        'pool_name' => $pool['name'],
                        'ranges' => $pool['ranges'],
                    ]);
                    $poolsImportedCount++; // Tambah hitungan pool yang diimpor
                }
            }

            return redirect()->route('ip-pools.index')
                ->with('success', "Berhasil mengimpor {$poolsImportedCount} IP Pool baru dari router {$router->name}.");

        } catch (\Exception $e) {
            return redirect()->route('ip-pools.index')
                ->with('error', 'Gagal terhubung atau mengambil data dari router: ' . $e->getMessage());
        }
    }
    
}
