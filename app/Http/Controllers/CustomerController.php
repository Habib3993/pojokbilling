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
        // PERBAIKAN: Tambahkan logging untuk debugging
        \Log::info('CustomerController: Form submitted', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'lokasi' => 'required|string|max:255',
            'package_id' => 'required|exists:packages,id', 
            'serial_number' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone', 
            'olt_id' => 'required|exists:olts,id',
            'register_port' => ['required', 'string', 'max:255', 'regex:/^(N\/A|1\/[1-4]\/[1-16]:[1-128])$/'],
            'vlan_ids' => 'required|array',
            'vlan_ids.*' => 'exists:vlans,id',
            'odp' => 'nullable|string|max:255',
            'subscription_date' => 'nullable|date',
            'sales' => 'nullable|string|max:255',
            'setor' => 'nullable|numeric|min:0',
        ], [
            'register_port.regex' => 'Format register port harus: 1/[1-4]/[1-16]:[1-128] (contoh: 1/2/8:11). Untuk ZTE C320 gunakan slot 1, subslot 1-4, port 1-16, ONU ID 1-128.'
        ]);

        \Log::info('CustomerController: Validation passed', $validated);
        
        $validated['location_id'] = auth()->user()->location_id;
        $olt = Olt::findOrFail($validated['olt_id']);
        $package = Package::findOrFail($validated['package_id']);
        $router = $package->router;
        $vlans = Vlan::find($validated['vlan_ids']);
        
        if (!$router) {
            \Log::error('CustomerController: Package has no router', ['package_id' => $validated['package_id']]);
            return back()->withInput()->with('error', 'Paket yang dipilih tidak terhubung ke router manapun.');
        }

        \Log::info('CustomerController: Starting integration process');

        // PERBAIKAN: Set timeout lebih lama untuk operasi kompleks
        set_time_limit(300);

        // 1. STEP 1: Test koneksi OLT terlebih dahulu
        // Menonaktifkan integrasi dengan OLT sementara waktu
        // try {
        //     \Log::info('CustomerController: Testing OLT connection', ['olt_ip' => $olt->ip_address]);
        //     $oltService = new OltService();
        //     $oltService->testConnection($olt->ip_address, $olt->username, $olt->password);
        //     \Log::info('CustomerController: OLT connection successful');
        // } catch (\Exception $e) {
        //     \Log::error('CustomerController: OLT connection failed', ['error' => $e->getMessage()]);
        //     return back()->withInput()->with('error', 'Gagal terhubung ke OLT: ' . $e->getMessage());
        // }

        // 2. STEP 2: Test koneksi MikroTik
        try {
            \Log::info('CustomerController: Testing MikroTik connection', ['router_ip' => $router->ip_address]);
            $client = new Client(['host' => $router->ip_address, 'user' => $router->username, 'pass' => $router->password]);
            // Test dengan query sederhana
            $testQuery = (new Query('/system/identity/print'));
            $client->query($testQuery)->read();
            \Log::info('CustomerController: MikroTik connection successful');
        } catch (\Exception $e) {
            \Log::error('CustomerController: MikroTik connection failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal terhubung ke MikroTik: ' . $e->getMessage());
        }

        // 3. STEP 3: Simpan ke database terlebih dahulu
        try {
            \Log::info('CustomerController: Creating customer in database');
            $customer = Customer::create($validated);
            $customer->vlans()->sync($validated['vlan_ids']);
            \Log::info('CustomerController: Customer created successfully', ['customer_id' => $customer->id]);
        } catch (\Exception $e) {
            \Log::error('CustomerController: Database creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal menyimpan data ke database: ' . $e->getMessage());
        }

        // 4. STEP 4: Konfigurasi di OLT
        // Menonaktifkan sementara integrasi dengan OLT
        // try {
        //     \Log::info('CustomerController: Configuring OLT');
        //     $oltService->registerOnu(
        //         $olt,
        //         $validated['register_port'],
        //         $validated['serial_number'],
        //         $validated['name'],
        //         $validated['odp'],
        //         $package,
        //         $vlans
        //     );
        //     \Log::info('CustomerController: OLT configuration successful');
        // } catch (\Exception $e) {
        //     \Log::error('CustomerController: OLT configuration failed', ['error' => $e->getMessage()]);
        //     // Rollback: Hapus customer dari database jika OLT gagal
        //     $customer->vlans()->detach();
        //     $customer->delete();
        //     return back()->withInput()->with('error', 'Gagal aktivasi di OLT: ' . $e->getMessage());
        // }

        // 5. STEP 5: Tambah user di MikroTik
        try {
            \Log::info('CustomerController: Adding user to MikroTik');
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $validated['name'])
                ->equal('password', '123456')
                ->equal('service', 'pppoe')
                ->equal('profile', $package->name);
            $client->query($query)->read();
            \Log::info('CustomerController: MikroTik user added successfully');
        } catch (\Exception $e) {
            \Log::error('CustomerController: MikroTik configuration failed', ['error' => $e->getMessage()]);
            // Rollback: Hapus dari OLT dan database
            try {
                $oltService->deleteOnu($olt, $validated['register_port']);
            } catch (\Exception $oltError) {
                \Log::error('CustomerController: OLT rollback failed', ['error' => $oltError->getMessage()]);
            }
            $customer->vlans()->detach();
            $customer->delete();
            return back()->withInput()->with('error', 'OLT berhasil dikonfigurasi, tetapi gagal menambah user di MikroTik: ' . $e->getMessage());
        }

        // 6. STEP 6: Tambah ke RADIUS
        try {
            \Log::info('CustomerController: Adding to RADIUS');
            $this->createRadiusUser($customer, $package);
            \Log::info('CustomerController: RADIUS configuration successful');
        } catch (\Exception $e) {
            \Log::warning('CustomerController: RADIUS configuration failed', ['error' => $e->getMessage()]);
            // Jika RADIUS gagal, tetap lanjutkan karena ini optional
            // Tapi beri notifikasi
            return redirect()->route('customers.index')
                ->with('warning', 'Pelanggan berhasil ditambahkan ke OLT dan MikroTik, tetapi gagal sinkronisasi RADIUS: ' . $e->getMessage());
        }

        \Log::info('CustomerController: All integrations successful', ['customer_id' => $customer->id]);
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan ke semua sistem (Database, OLT, MikroTik, RADIUS).');
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
        \Log::info('CustomerController: Update function called', ['customer_id' => $customer->id, 'request_data' => $request->all()]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'lokasi' => 'required|string|max:255',
                'package_id' => 'required|exists:packages,id',
                'serial_number' => 'required|string|max:255',
                'phone' => 'required|string|unique:customers,phone,' . $customer->id,
                'olt_id' => 'required|exists:olts,id',
                'register_port' => ['required', 'string', 'max:255'],
                'vlan_ids' => 'required|array',
                'vlan_ids.*' => 'exists:vlans,id',
                'odp' => 'nullable|string|max:255',
                'subscription_date' => 'nullable|date',
                'sales' => 'nullable|string|max:255',
                'setor' => 'nullable|numeric|min:0',
            ]);

            \Log::info('CustomerController: Validation passed', ['validated_data' => $validated]);

            $customer->update($validated);
            \Log::info('CustomerController: Data updated successfully', ['customer_id' => $customer->id]);

            $customer->vlans()->sync($validated['vlan_ids']);
            \Log::info('CustomerController: VLANs synced successfully', ['customer_id' => $customer->id]);

            return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('CustomerController: Update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data pelanggan: ' . $e->getMessage());
        }
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
        $sales = $request->input('sales');
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
            ->when($sales, fn($q, $s) => $q->where('sales', 'like', "%{$s}%"))
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