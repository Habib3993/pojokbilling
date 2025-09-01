<?php

namespace App\Http\Controllers;

use App\Models\Vlan;
use Illuminate\Http\Request;

class VlanController extends Controller
{
    public function index()
    {
        $vlans = Vlan::latest()->paginate(10);
        return view('vlans.index', compact('vlans'));
    }
    

    public function create()
    {
        return view('vlans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vlan_id' => 'required|integer|unique:vlans,vlan_id',
            'name' => 'nullable|string|max:255',
        ]);

        Vlan::create($request->all());
        return redirect()->route('vlans.index')->with('success', 'VLAN berhasil ditambahkan.');
    }

    public function edit(Vlan $vlan)
    {
        return view('vlans.edit', compact('vlan'));
    }

    public function update(Request $request, Vlan $vlan)
    {
        $request->validate([
            'vlan_id' => 'required|integer|unique:vlans,vlan_id,' . $vlan->id,
            'name' => 'nullable|string|max:255',
        ]);

        $vlan->update($request->all());
        return redirect()->route('vlans.index')->with('success', 'VLAN berhasil diperbarui.');
    }

    public function destroy(Vlan $vlan)
    {
        $vlan->delete();
        return redirect()->route('vlans.index')->with('success', 'VLAN berhasil dihapus.');
    }
}
