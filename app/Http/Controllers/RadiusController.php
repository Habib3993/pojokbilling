<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use App\Models\Router; // Pastikan ini ada
use Illuminate\Http\Request;

class RadiusController extends Controller
{
    /**
     * Menampilkan daftar semua Klien NAS.
     */
    public function index()
    {
        // PERBAIKAN FINAL: Ganti latest() menjadi orderBy('id', 'desc')
        $nasClients = Nas::orderBy('id', 'desc')->paginate(10);
        return view('radius.index', compact('nasClients'));
    }

    /**
     * Menampilkan form untuk menambah Klien NAS baru dari daftar router yang ada.
     */
    public function create()
    {
        $registeredNasIps = Nas::pluck('nasname')->toArray();
        $routers = Router::whereNotIn('ip_address', $registeredNasIps)->get();
        return view('radius.create', compact('routers'));
    }

    /**
     * Menyimpan Klien NAS baru ke database dari router yang dipilih.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'router_id' => 'required|exists:routers,id',
            'secret' => 'required|string|max:60',
            'description' => 'nullable|string|max:200',
        ]);

        $router = Router::findOrFail($validated['router_id']);

        if (Nas::where('nasname', $router->ip_address)->exists()) {
            return back()->with('error', 'Router ini sudah terdaftar sebagai Klien NAS.');
        }

        Nas::create([
            'nasname' => $router->ip_address,
            'shortname' => $router->name,
            'secret' => $validated['secret'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('radius.index')->with('success', 'Router berhasil ditambahkan sebagai Klien NAS.');
    }

    /**
     * Menampilkan form untuk mengedit Klien NAS.
     */
    public function edit(Nas $nas)
    {
        return view('radius.edit', compact('nas'));
    }

    /**
     * Mengupdate Klien NAS di database.
     */
    public function update(Request $request, Nas $nas)
    {
        $validated = $request->validate([
            'nasname' => 'required|ip|unique:nas,nasname,' . $nas->id,
            'shortname' => 'required|string|max:32',
            'secret' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:200',
        ]);

        if (empty($validated['secret'])) {
            unset($validated['secret']);
        }

        $nas->update($validated);

        return redirect()->route('radius.index')->with('success', 'Klien NAS berhasil diperbarui.');
    }

    /**
     * Menghapus Klien NAS dari database.
     */
    public function destroy(Nas $nas)
    {
        $nas->delete();
        return redirect()->route('radius.index')->with('success', 'Klien NAS berhasil dihapus.');
    }
}
