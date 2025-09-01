<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Olt;
use App\Models\Package;
use App\Traits\RadiusService;
use App\Models\Router;
use App\Models\Vlan;
use Illuminate\Http\Request;
use App\Services\OltService;
use Carbon\Carbon;
use RouterOS\Client;
use RouterOS\Query;

class CustomerController extends Controller
{
    use RadiusService;
    
    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = auth()->user();
        $query = Customer::query();

        if (!$user->hasRole('superadmin')) {
            $query->where('location_id', $user->location_id);
        }

        // KESALAHAN KETIK DI SINI: Seharusnya $query->with(), bukan $query::with()
        // PERBAIKAN: Menggunakan -> untuk memanggil method pada objek $query
        $customers = $query->with(['package', 'olt', 'vlans'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
            })
            ->latest('id')
            ->get();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $packages = Package::all();
        $olts = Olt::all();
        $vlans = Vlan::all();
        return view('customers.create', compact('packages', 'olts', 'vlans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'lokasi' => 'required|string|max:255',
            'package_id' => 'required|exists:packages,id', 
            'serial_number' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone', 
            'olt_id' => 'required|exists:olts,id',
            'register_port' => 'required|string|max:255',
            'vlan_ids' => 'required|array',
            'vlan_ids.*' => 'exists:vlans,id',
            'odp' => 'nullable|string|max:255',
            'subscription_date' => 'nullable|date',
            'sales' => 'nullable|string|max:255',
            'setor' => 'nullable|numeric|min:0',
        ]);
        $validated['location_id'] = auth()->user()->location_id;
        $olt = Olt::findOrFail($validated['olt_id']);
        $package = Package::findOrFail($validated['package_id']);
        $router = $package->router;
        $vlans = Vlan::find($validated['vlan_ids']);
        if (!$router) {
            return back()->withInput()->with('error', 'Paket yang dipilih tidak terhubung ke router manapun.');
        }

        try {
            $client = new Client(['host' => $router->ip_address, 'user' => $router->username, 'pass' => $router->password]);
            $query = (new Query('/ppp/secret/add'))->equal('name', $validated['name'])->equal('password', '123456')->equal('service', 'pppoe')->equal('profile', $package->name);
            $client->query($query)->read();
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Aktivasi OLT berhasil, tetapi gagal menambah user di MikroTik: ' . $e->getMessage());
        }
        $customer = Customer::create($validated);
        $customer->vlans()->sync($validated['vlan_ids']);
        
        try {
            (new OltService())->registerOnu(
                $olt,
                $validated['register_port'],
                $validated['serial_number'],
                $validated['name'],
                $validated['odp'],
                $package,
                $vlans
            );
        } catch (\Exception $e) {
            // Jika gagal, user di MikroTik bisa dihapus kembali (rollback) jika diperlukan
            return back()->withInput()->with('error', 'Gagal aktivasi di OLT: ' . $e->getMessage());
        }
        $this->createRadiusUser($customer, $package);

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }


    public function edit(Customer $customer)
    {
        $packages = Package::all();
        $olts = Olt::all();
        $vlans = Vlan::all();
        $customer->load('vlans'); 
        return view('customers.edit', compact('customer', 'packages', 'olts', 'vlans'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'package_id' => 'required|exists:packages,id',
            'serial_number' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $customer->id,
            'olt_id' => 'required|exists:olts,id',
            'register_port' => 'required|string|max:255',
            'vlan_ids' => 'required|array',
            'vlan_ids.*' => 'exists:vlans,id',
            'odp' => 'nullable|string|max:255',
            'subscription_date' => 'nullable|date',
            'sales' => 'nullable|string|max:255',
            'setor' => 'nullable|numeric|min:0',
        ]);
        
        $oltService = new OltService();
        $newPackage = Package::findOrFail($validated['package_id']);
        $newOlt = Olt::findOrFail($validated['olt_id']);
        $newVlans = Vlan::find($validated['vlan_ids']);
        $router = $newPackage->router;

        if ($customer->olt_id != $validated['olt_id'] || $customer->register_port != $validated['register_port']) {
            try {
                $oltService->updateOnu($customer->olt, $customer->register_port, $newOlt, $validated['register_port'], $validated['serial_number'], $validated['name'], $newVlans);
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Gagal update konfigurasi di OLT: ' . $e->getMessage());
            }
        }

        if ($customer->package_id != $validated['package_id'] || $customer->name != $validated['name']) {
            try {
                $client = new Client(['host' => $router->ip_address, 'user' => $router->username, 'pass' => $router->password]);
                $query = (new Query('/ppp/secret/print'))->where('name', $customer->name)->operations('=.id');
                $secretId = $client->query($query)->read()[0]['.id'] ?? null;
                if ($secretId) {
                    $updateQuery = (new Query('/ppp/secret/set'))->equal('.id', $secretId)->equal('name', $validated['name'])->equal('profile', $newPackage->name);
                    $client->query($updateQuery)->read();
                }
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Gagal update profil di MikroTik: ' . $e->getMessage());
            }
        }

        $customer->update($validated);
        $customer->vlans()->sync($validated['vlan_ids']);
        
        $this->updateRadiusUser($customer->fresh(), $newPackage);

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        set_time_limit(300);
        $oltService = new \App\Services\OltService();
        $router = $customer->package->router;

        try {
            if ($customer->olt && $customer->register_port) {
                $oltService->deleteOnu($customer->olt, $customer->register_port);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dari OLT: ' . $e->getMessage());
        }

        try {
            if ($router) {
                $client = new \RouterOS\Client(['host' => $router->ip_address, 'user' => $router->username, 'pass' => $router->password]);
                $query = (new \RouterOS\Query('/ppp/secret/print'))->where('name', $customer->name)->operations('=.id');
                $secretIdResponse = $client->query($query)->read();

                if (!empty($secretIdResponse) && isset($secretIdResponse[0]['.id'])) {
                    $secretId = $secretIdResponse[0]['.id'];
                    $removeQuery = (new \RouterOS\Query('/ppp/secret/remove'))->equal('.id', $secretId);
                    $client->query($removeQuery)->read();
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dari MikroTik: ' . $e->getMessage());
        }
        
        $this->deleteRadiusUser($customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus dari semua sistem.');
    }
    
    public function active(Request $request)
    {
        $search = $request->input('search');
        $today = \Carbon\Carbon::now()->toDateString();
        $user = auth()->user();

        $query = Customer::query();
        if (!$user->hasRole('superadmin')) {
            $query->where('location_id', $user->location_id);
        }

        // KESALAHAN LOGIKA DI SINI: Anda memulai query baru, bukan melanjutkan $query yang sudah difilter.
        // PERBAIKAN: Lanjutkan dari variabel $query
        $customers = $query->with(['package', 'latestPayment'])
            ->where('active_until', '>=', $today)
            ->when($search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->get();
            
        return view('customers.status_list', [
            'customers' => $customers,
            'title' => 'Pelanggan Aktif',
            'status' => 'active'
        ]);
    }

    public function inactive(Request $request)
    {
        $search = $request->input('search');
        $today = \Carbon\Carbon::now()->toDateString();
        $user = auth()->user();
        
        $query = Customer::query();
        if (!$user->hasRole('superadmin')) {
            $query->where('location_id', $user->location_id);
        }

        // KESALAHAN LOGIKA DI SINI: Sama seperti fungsi active().
        // PERBAIKAN: Lanjutkan dari variabel $query
        $customers = $query->with(['package', 'latestPayment'])
            ->where(function ($q) use ($today) {
                $q->where('active_until', '<', $today)->orWhereNull('active_until');
            })
            ->when($search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->get();
            
        return view('customers.status_list', [
            'customers' => $customers,
            'title' => 'Pelanggan Tidak Aktif',
            'status' => 'inactive'
        ]);
    }

    public function syncWithMikrotik(Request $request)
    {
        $statusToSync = $request->input('status');
        $today = Carbon::now()->toDateString();
        $user = auth()->user();
        
        // KESALAHAN LOGIKA DI SINI: Anda tidak menggunakan query yang sudah difilter.
        // PERBAIKAN: Kita akan membangun query berdasarkan status, lalu menambah filter lokasi.
        $baseQuery = Customer::query()->with('package.router');

        if ($statusToSync === 'active') {
            $baseQuery->where('active_until', '>=', $today);
            $action = 'enable';
        } elseif ($statusToSync === 'inactive') {
            $baseQuery->where(fn($q) => $q->where('active_until', '<', $today)->orWhereNull('active_until'));
            $action = 'disable';
        } else {
             return back()->with('error', 'Status sinkronisasi tidak valid.');
        }

        // Terapkan filter lokasi setelah kondisi status
        if (!$user->hasRole('superadmin')) {
            $baseQuery->where('location_id', $user->location_id);
        }
        
        $customers = $baseQuery->get();
        $results = [];
        foreach ($customers as $customer) {
            $router = $customer->package->router ?? null;
            if (!$router) {
                $results[] = "{$customer->name}: Gagal (Paket tidak terhubung ke router)";
                continue;
            }

            try {
                $client = new Client(['host' => $router->ip_address, 'user' => $router->username, 'pass' => $router->password]);
                $query = (new Query('/ppp/secret/print'))->where('name', $customer->name)->operations('=.id');
                $secretId = $client->query($query)->read()[0]['.id'] ?? null;

                if ($secretId) {
                    $syncQuery = (new Query("/ppp/secret/{action}"))->equal('.id', $secretId);
                    $client->query($syncQuery)->read();
                    $results[] = "{$customer->name}: Sukses ({$action}d)";
                } else {
                    $results[] = "{$customer->name}: Gagal (User tidak ditemukan di MikroTik)";
                }
            } catch (\Exception $e) {
                $results[] = "{$customer->name}: Gagal ({$e->getMessage()})";
            }
        }
        return back()->with('sync_results', $results);
    }

    public function syncRadius(Customer $customer)
    {
        // Query yang Anda buat di sini tidak diperlukan karena $customer sudah didapat dari Route-Model Binding.
        // Jadi saya hapus agar lebih bersih.
        if (!$customer->package) {
            return back()->with('error', 'Gagal sinkronisasi: Pelanggan tidak memiliki paket internet.');
        }
        $this->updateRadiusUser($customer, $customer->package);
        return back()->with('success', 'Pelanggan ' . $customer->name . ' berhasil disinkronkan ke RADIUS.');
    }

    public function show(Customer $customer)
    {
        //
    }
    
}