<?php

namespace App\Http\Controllers;

use App\Models\LayerGroup;
use Illuminate\Http\Request;

class LayerGroupController extends Controller
{
    public function index()
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        $groups = LayerGroup::latest()->get();
        return view('layer_groups.index', compact('groups'));
    }

    public function create()
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }
        $availableIcons = [
            'fa-solid fa-circle-xmark',
            'fa-solid fa-stop',
            'fa-solid fa-code-fork',
            'fa-solid fa-thumbtack',
            'fa-solid fa-location-pin',
            'fa-solid fa-location-dot',
            'fa-solid fa-server',
            'fa-regular fa-circle',
            'fa-solid fa-diagram-project',
        ];

        return view('layer_groups.create', compact('availableIcons'));
    }

    public function store(Request $request)
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:layer_groups,name',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:100'
        ]);
        
        LayerGroup::create($validated);
        return redirect()->route('layer-groups.index')->with('success', 'Grup Layer berhasil dibuat.');
    }

    public function edit(LayerGroup $layerGroup)
    {
        
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }
        $availableIcons = [
            'fa-solid fa-circle-xmark',
            'fa-solid fa-stop',
            'fa-solid fa-code-fork',
            'fa-solid fa-thumbtack',
            'fa-solid fa-location-pin',
            'fa-solid fa-location-dot',
            'fa-solid fa-server',
            'fa-regular fa-circle', // Menggunakan fa-regular fa-circle sebagai contoh
            'fa-solid fa-diagram-project', // Mengganti fa-regular fa-diagram-project
        ];

        return view('layer_groups.edit', compact('layerGroup', 'availableIcons'));
    }

    public function update(Request $request, LayerGroup $layerGroup)
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:layer_groups,name,' . $layerGroup->id,
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:100'
        ]);
        
        $layerGroup->update($validated);
        return redirect()->route('layer-groups.index')->with('success', 'Grup Layer berhasil diperbarui.');
    }

    public function destroy(LayerGroup $layerGroup)
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        $layerGroup->delete();
        return redirect()->route('layer-groups.index')->with('success', 'Grup Layer berhasil dihapus.');
    }
}