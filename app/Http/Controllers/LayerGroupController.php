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

        return view('layer_groups.create');
    }

    public function store(Request $request)
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        $request->validate(['name' => 'required|string|unique:layer_groups,name']);
        LayerGroup::create($request->all());
        return redirect()->route('layer-groups.index')->with('success', 'Grup Layer berhasil dibuat.');
    }

    public function edit(LayerGroup $layerGroup)
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        return view('layer_groups.edit', compact('layerGroup'));
    }

    public function update(Request $request, LayerGroup $layerGroup)
    {
        // PENJAGA: Cek apakah user adalah superadmin
        if (!auth()->check() || !auth()->user()->hasRole('superadmin')) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        $request->validate(['name' => 'required|string|unique:layer_groups,name,' . $layerGroup->id]);
        $layerGroup->update($request->all());
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