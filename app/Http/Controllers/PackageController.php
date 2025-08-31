<?php

namespace App\Http\Controllers;

use App\Models\IpPool;
use App\Models\Package;
use App\Models\Router;
use Illuminate\Http\Request;
use RouterOS\Client;
use RouterOS\Query;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $routers = Router::all(); // sync router

        // REVISI: Tambahkan 'with' untuk eager loading agar lebih efisien
        $packages = Package::with(['router', 'ipPool'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->get();
        
        return view('packages.index', compact('packages', 'routers'));
    }

    public function create()
    {
        $routers = Router::all();
        $ipPools = IpPool::all();
        return view('packages.create', compact('routers', 'ipPools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packages,name',
            'speed' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'router_id' => 'required|exists:routers,id',
            'ip_pool_id' => 'required|exists:ip_pools,id',
        ]);

        $router = Router::findOrFail($validated['router_id']);
        $ipPool = IpPool::findOrFail($validated['ip_pool_id']);

        try {
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
            ]);
            
            $query = (new Query('/ppp/profile/add'))
                ->equal('name', $validated['name'])
                ->equal('rate-limit', $validated['speed'])
                ->equal('remote-address', $ipPool->pool_name);

            $client->query($query)->read();

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat profil di MikroTik: ' . $e->getMessage());
        }
        
        Package::create($validated);

        return redirect()->route('packages.index')->with('success', 'Paket berhasil ditambahkan di aplikasi dan MikroTik.');
    }

    public function edit(Package $package)
    {
        $routers = Router::all();
        $ipPools = IpPool::all();
        return view('packages.edit', compact('package', 'routers', 'ipPools'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'speed' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'router_id' => 'required|exists:routers,id',
            'ip_pool_id' => 'required|exists:ip_pools,id',
        ]);

        $oldPackageName = $package->name;
        $router = Router::findOrFail($validated['router_id']);
        $ipPool = IpPool::findOrFail($validated['ip_pool_id']);

        try {
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int)$router->port,
            ]);

            $query = (new Query('/ppp/profile/print'))->where('name', $oldPackageName);
            $profileResponse = $client->query($query)->read();

            if (!empty($profileResponse) && isset($profileResponse[0]['.id'])) {
                $profileId = $profileResponse[0]['.id'];
                $updateQuery = (new Query('/ppp/profile/set'))
                    ->equal('.id', $profileId)
                    ->equal('name', $validated['name'])
                    ->equal('rate-limit', $validated['speed'])
                    ->equal('remote-address', $ipPool->pool_name);
                $client->query($updateQuery)->read();
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui profil di MikroTik: ' . $e->getMessage());
        }
        
        $package->update($validated);

        return redirect()->route('packages.index')->with('success', 'Paket berhasil diperbarui.');
    }
    public function destroy(Package $package)
    {
        // REVISI: Tambahkan pengecekan apakah paket masih digunakan
        if ($package->customers()->count() > 0) {
            return back()->with('error', "Tidak bisa menghapus paket '{$package->name}' karena masih digunakan oleh pelanggan.");
        }

        // Ambil data router yang terhubung dengan paket ini
        $router = $package->router;

        // Jika paket terhubung ke router, coba hapus profil di MikroTik
        if ($router) {
            try {
                $client = new \RouterOS\Client([
                    'host' => $router->ip_address,
                    'user' => $router->username,
                    'pass' => $router->password,
                ]);

                // 1. Cari ID dari PPP Profile yang akan dihapus
                $query = (new \RouterOS\Query('/ppp/profile/print'))
                    ->where('name', $package->name)
                    ->operations('=.id');
                
                $profileIdResponse = $client->query($query)->read();

                // 2. Jika profil ditemukan, kirim perintah hapus
                if (!empty($profileIdResponse) && isset($profileIdResponse[0]['.id'])) {
                    $profileId = $profileIdResponse[0]['.id'];
                    $removeQuery = (new \RouterOS\Query('/ppp/profile/remove'))
                        ->equal('.id', $profileId);
                    
                    $client->query($removeQuery)->read();
                }

            } catch (\Exception $e) {
                // Jika gagal terhubung atau menghapus dari MikroTik, batalkan dan beri pesan error
                return back()->with('error', 'Gagal menghapus profil dari MikroTik: ' . $e->getMessage());
            }
        }

        // Jika berhasil (atau jika tidak terhubung ke router), baru hapus dari database lokal
        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paket berhasil dihapus dari aplikasi dan MikroTik.');
    }
    public function sync($routerId)
    {
        $router = Router::findOrFail($routerId);

        try {
            // 1. Terhubung ke router MikroTik
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int)$router->port,
            ]);

            // 2. Ambil semua PPP Profiles dari MikroTik
            $mikrotikPackages = $client->query('/ppp/profile/print')->read();

            // 3. Ambil nama paket yang sudah ada di database LOKAL untuk router ini
            $existingPackageNames = Package::where('router_id', $router->id)
                                    ->pluck('name')
                                    ->toArray();

            $packagesImportedCount = 0;

            // 4. Loop setiap paket dari MikroTik dan simpan jika belum ada
            foreach ($mikrotikPackages as $pkg) {
                if (isset($pkg['name']) && !in_array($pkg['name'], $existingPackageNames)) {
                    
                    // --- PERBAIKAN DI SINI ---
                    // Cari IP Pool yang sesuai di database lokal berdasarkan nama
                    $poolName = $pkg['remote-address'] ?? null;
                    $ipPool = null;
                    if ($poolName) {
                        $ipPool = IpPool::where('pool_name', $poolName)
                                        ->where('router_id', $router->id)
                                        ->first();
                    }

                    // Hanya buat paket jika IP Pool-nya ditemukan di database lokal
                    if ($ipPool) {
                        Package::create([
                            'router_id' => $router->id,
                            'name' => $pkg['name'],
                            'speed' => $pkg['rate-limit'] ?? 'N/A',
                            'price' => 0,
                            'ip_pool_id' => $ipPool->id, // Gunakan ID dari pool yang ditemukan
                        ]);
                        $packagesImportedCount++;
                    }
                }
            }

            return redirect()->route('packages.index')
                ->with('success', "Berhasil mengimpor {$packagesImportedCount} paket baru dari router {$router->name}. Paket yang IP Pool-nya tidak ditemukan di database akan dilewati.");

        } catch (\Exception $e) {
            return redirect()->route('packages.index')
                ->with('error', 'Gagal terhubung atau mengambil data dari router: ' . $e->getMessage());
        }
    }

}
