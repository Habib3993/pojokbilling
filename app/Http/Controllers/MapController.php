<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Odp; // 1. Import model Odp
use Illuminate\Http\Request;
use App\Models\InventoryMap;

class MapController extends Controller
{
    public function index()
    {
        // --- Mengambil dan memproses data pelanggan ---
        $customersWithCoords = Customer::with('package')
            ->whereNotNull('lokasi')->where('lokasi', '!=', '')->get();

        $customers = $customersWithCoords->map(function ($customer) {
            $coords = explode(',', $customer->lokasi);
            if (count($coords) === 2) {
                $customer->lat = trim($coords[0]);
                $customer->lng = trim($coords[1]);
            }
            return $customer;
        })->filter(fn ($customer) => isset($customer->lat) && isset($customer->lng))->values();

        // --- 2. Mengambil dan memproses data ODP ---
        $odpsWithCoords = Odp::whereNotNull('lokasi')->where('lokasi', '!=', '')->get();

        $odps = $odpsWithCoords->map(function ($odp) {
            $coords = explode(',', $odp->lokasi);
            if (count($coords) === 2) {
                $odp->lat = trim($coords[0]);
                $odp->lng = trim($coords[1]);
            }
            return $odp;
        })->filter(fn ($odp) => isset($odp->lat) && isset($odp->lng));

        // 3. Kirim kedua jenis data ke view
        return view('map.index', compact('customers', 'odps'));
    }
    public function store(Request $request)
    {
        // validasi dulu
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'layer_group_id' => 'nullable|integer',
            'color' => 'nullable|string|max:20',
            'coordinates' => 'required|string', // lat,lng
            'description' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);

        $map = InventoryMap::create($validated);

        return response()->json([
            'success' => true,
            'data' => $map
        ]);
    }

}
