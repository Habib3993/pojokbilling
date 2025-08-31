<?php

namespace App\Http\Controllers;

use App\Models\MapPoint;
use App\Models\MapPolyline;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\LayerGroup;

class InventoryMapController extends Controller
{
    
    public function index()
    {
        // Ambil semua titik dan kelompokkan berdasarkan 'LayerGroup'
        $layerGroups = LayerGroup::with('mapPoints')->get();
        // Ambil semua garis (tidak perlu dikelompokkan)
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

    public function storePoint(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'layer_group_id' => 'required|exists:layer_groups,id',
            'color' => 'required|string|max:255',
            'coordinates' => 'required|string',
            'location_id' => 'nullable|exists:locations,id'
            
        ]);

        $point = MapPoint::create($validated);
        $point->load('layerGroup');
        return response()->json([
            'success' => true,
            'point' => $point
        ]);
    }

    public function storePolyline(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'path' => 'required|json',
        ]);

        $polyline = MapPolyline::create($validated);
        return response()->json($polyline);
    }

    public function updatePoint(Request $request, MapPoint $mapPoint)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'layer_group_id' => 'required|exists:layer_groups,id',
            'color' => 'required|string|max:255',
            'coordinates' => 'sometimes|string',
            'description' => 'nullable|string',
        ]);

        $mapPoint->update($validated);

        return response()->json($mapPoint);
    }

    public function destroyPoint(MapPoint $mapPoint)
    {
        $mapPoint->delete();

        return response()->json(['success' => true, 'message' => 'Titik berhasil dihapus.']);
    }

    /**
     * REVISI: Tambahkan fungsi ini untuk meng-update garis dari pop-up peta.
     */
    public function updatePolyline(Request $request, MapPolyline $mapPolyline)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'path' => 'sometimes|json',
        ]);

        $mapPolyline->update($validated);

        return response()->json($mapPolyline);
    }

    /**
     * REVISI: Tambahkan fungsi ini untuk menghapus garis dari pop-up peta.
     */
    public function destroyPolyline(MapPolyline $mapPolyline)
    {
        $mapPolyline->delete();

        return response()->json(['success' => true, 'message' => 'Garis berhasil dihapus.']);
    }
}
