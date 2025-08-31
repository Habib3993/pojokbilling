<?php

namespace App\Http\Controllers;

use App\Models\GenieAcsServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GenieAcsServerController extends Controller
{
    public function index()
    {
        $servers = GenieAcsServer::latest()->paginate(10);

        return view('genieacs_servers.index', compact('servers'));
    }

    public function create()
    {
        return view('genieacs_servers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        GenieAcsServer::create($validated);
        return redirect()->route('genieacs-servers.index')->with('success', 'Server GenieACS berhasil ditambahkan.');
    }


    // Menampilkan halaman monitoring ONT untuk server tertentu
    public function show(GenieAcsServer $genieAcsServer)
    {
        try {
            // Gunakan URL langsung dari database tanpa pembersihan rumit
            $baseUrl = rtrim($genieAcsServer->url, '/');

            // Pastikan URL tidak kosong setelah diambil dari DB
            if (empty($baseUrl)) {
                return back()->with('error', 'URL untuk server ini kosong di database.');
            }

            // Langsung coba login menggunakan format form
            $loginResponse = Http::timeout(15)
                ->asForm()
                ->post($baseUrl . '/login', [
                    'username' => $genieAcsServer->username,
                    'password' => $genieAcsServer->password,
                ]);

            if (!$loginResponse->successful()) {
                return back()->with('error', 'Gagal login ke GenieACS. Periksa kredensial. Status: ' . $loginResponse->status());
            }

            $authCookies = $loginResponse->cookies();

            // Ambil data perangkat
            $response = Http::timeout(15)
                ->withCookies($authCookies->toArray(), parse_url($baseUrl, PHP_URL_HOST))
                ->get($baseUrl . '/devices/', [
                    'projection' => '_id,_deviceId._SerialNumber'
                ]);

            if (!$response->successful()) {
                return back()->with('error', 'Login berhasil, tetapi gagal mengambil data perangkat.');
            }

            $devices = $response->json();
            return view('genieacs_servers.show', ['server' => $genieAcsServer, 'devices' => $devices, 'error' => null]);

        } catch (\Exception $e) {
            // Catat error sebenarnya untuk debugging
            \Illuminate\Support\Facades\Log::error('Error Final di GenieAcsServerController: ' . $e->getMessage());
            // Tampilkan pesan yang lebih umum ke user
            return back()->with('error', 'Terjadi kesalahan. Periksa log untuk detail.');
        }
    }
    public function edit(GenieAcsServer $genieAcsServer)
    {
        return view('genieacs_servers.edit', ['server' => $genieAcsServer]);
    }

    public function update(Request $request, GenieAcsServer $genieAcsServer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        
        $genieAcsServer->update($validated);
        return redirect()->route('genieacs-servers.index')->with('success', 'Server GenieACS berhasil diperbarui.');
    }

    public function destroy(GenieAcsServer $genieAcsServer)
    {
        $genieAcsServer->delete();
        return redirect()->route('genieacs-servers.index')->with('success', 'Server GenieACS berhasil dihapus.');
    }

}
