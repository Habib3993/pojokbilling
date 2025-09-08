<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use Illuminate\Http\Request;
use App\Services\OltService;
use phpseclib3\Net\SSH2;

class OltController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $olts = Olt::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('ip_address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
            
        return view('olts.index', compact('olts'));
    }

    public function create()
    {
        return view('olts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|ip|unique:olts,ip_address',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        // --- BAGIAN DEBUGGING YANG DIPERBAIKI ---
        try {
            $oltService = new OltService();
            $oltService->testConnection($validated['ip_address'], $validated['username'], $validated['password']);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal terhubung ke OLT. Pesan: ' . $e->getMessage());
        }
        
        // Jika koneksi berhasil, baru simpan data
        Olt::create($validated);

        return redirect()->route('olts.index')->with('success', 'OLT berhasil ditambahkan dan koneksi terverifikasi.');
    }
    /**
     * Display the specified resource.
     * (Kita belum menggunakan ini, tapi perlu ada agar tidak error)
     */
    public function show(Olt $olt)
    {
        //
    }

    public function edit(Olt $olt)
    {
        return view('olts.edit', compact('olt'));
    }

    public function update(Request $request, Olt $olt)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|ip|unique:olts,ip_address,' . $olt->id,
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
        ]);

        // Ambil password baru jika diisi, jika tidak, gunakan password lama
        $newPassword = $request->filled('password') ? $validated['password'] : $olt->password;

        // Coba koneksi dengan data baru sebelum menyimpan
        try {
            (new OltService())->testConnection(
                $validated['ip_address'],
                $validated['username'],
                $newPassword
            );
        } catch (\Exception $e) {
            // Jika koneksi gagal, kembali ke form edit dengan pesan error
            return back()->withInput()->with('error', 'Gagal terhubung ke OLT dengan kredensial baru: ' . $e->getMessage());
        }
        
        // Jika koneksi berhasil, siapkan data untuk disimpan
        $data = $request->except('password');
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $olt->update($data);

        return redirect()->route('olts.index')->with('success', 'OLT berhasil diperbarui dan koneksi terverifikasi.');
    }

    public function destroy(Olt $olt)
    {
        $olt->delete();

        return redirect()->route('olts.index')->with('success', 'OLT berhasil dihapus.');
    }
    
}
