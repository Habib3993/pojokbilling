<?php

namespace App\Http\Controllers;

use App\Models\MapPoint;
use App\Models\MapPolyline;
use App\Models\Location;
use App\Models\LayerGroup;
use Illuminate\Http\Request;

class InventoryMapController extends Controller
{
    public function index()
    {
        $layerGroups = LayerGroup::with('mapPoints')->get();
        $polylines = MapPolyline::all();
        $locations = Location::all();

        $user = auth()->user();
        $permissions = [
            'canCreate' => $user->can('create map_data'),
            'canEdit'   => $user->can('edit map_data'),
            'canDelete' => $user->can('delete map_data'),
        ];
        
        return view('inventory_map.index', compact('layerGroups', 'polylines', 'locations', 'permissions'));
    }

    /**
     * REVISI LENGKAP: Menyimpan titik baru.
     */
    public function storePoint(Request $request)
    {
        // 1. Validasi data yang benar-benar dikirim dari form.
        // 'color' tidak lagi divalidasi karena akan kita ambil dari grup.
        $validatedData = $request->validate([
            'name'           => 'required|string|max:255',
            'layer_group_id' => 'required|exists:layer_groups,id',
            'coordinates'    => 'required|string',
            'description'    => 'nullable|string', // Aturan untuk deskripsi ditambahkan
            'location_id'    => 'nullable|exists:locations,id'
        ]);

        // 2. Ambil data 'color' dari LayerGroup yang dipilih.
        // Ini memastikan setiap titik baru akan mengikuti style grupnya.
        $layerGroup = LayerGroup::find($validatedData['layer_group_id']);
        $groupStyle = [
            // Ambil warna grup, atau gunakan warna biru default jika warna di grup belum diatur.
            'color' => $layerGroup->color ?? '#3b82f6', 
        ];

        // 3. Gabungkan data dari form dengan data style dari grup.
        $completeData = array_merge($validatedData, $groupStyle);

        // 4. Buat record baru di database menggunakan data yang sudah lengkap.
        $point = MapPoint::create($completeData);

        // 5. Muat relasi untuk dikirim kembali ke JavaScript agar UI bisa di-update.
        $point->load('layerGroup');
        
        return response()->json([
            'success' => true,
            'point'   => $point
        ]);
    }

    public function storePolyline(Request $request)
    {
        // Validasi untuk polyline sudah benar, tidak perlu diubah.
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'required|string',
            'path'        => 'required|json',
            'description' => 'nullable|string', // Pastikan validasi deskripsi juga ada di sini
        ]);

        $polyline = MapPolyline::create($validated);
        return response()->json($polyline); // Mengembalikan data lengkap setelah dibuat
    }

    public function updatePoint(Request $request, MapPoint $mapPoint)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'coordinates' => 'sometimes|string',
        ]);

        $mapPoint->update($validated);
        $mapPoint->load('layerGroup');

        return response()->json([
            'success' => true,
            'point'   => $mapPoint
        ]);
    }

    public function updatePolyline(Request $request, MapPolyline $mapPolyline)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'sometimes|required|string|max:255',
            'path'        => 'sometimes|json',
        ]);

        $mapPolyline->update($validated);

        return response()->json([
            'success'  => true,
            'polyline' => $mapPolyline
        ]);
    }

    public function destroyPoint(MapPoint $mapPoint)
    {
        $mapPoint->delete();
        return response()->json(['success' => true, 'message' => 'Titik berhasil dihapus.']);
    }

    public function destroyPolyline(MapPolyline $mapPolyline)
    {
        $mapPolyline->delete();
        return response()->json(['success' => true, 'message' => 'Garis berhasil dihapus.']);
    }
}