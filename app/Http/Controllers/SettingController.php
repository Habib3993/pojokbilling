<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan umum.
     */
    public function general()
    {
        // Ambil semua setting dari database dan ubah menjadi array
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('settings.general', compact('settings'));
    }

    /**
     * Menyimpan pengaturan umum.
     */
    public function storeGeneral(Request $request)
    {
        // 1. Validasi semua input yang diharapkan dari form
        $validated = $request->validate([
            // Identitas Aplikasi
            'app_name' => 'nullable|string|max:255',
            'app_address' => 'nullable|string',
            'app_phone' => 'nullable|string|max:20',
            'app_email' => 'nullable|email|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi untuk logo

            // Pengaturan Keuangan
            'app_currency_symbol' => 'nullable|string|max:10',
            'app_tax_percentage' => 'nullable|numeric|min:0|max:100',

            // Pengaturan Invoice
            'invoice_footer_text' => 'nullable|string',
            'invoice_prefix' => 'nullable|string|max:20',

            // API WhatsApp Gateway (yang sudah ada)
            'wa_gateway_url' => 'nullable|url',
            'wa_gateway_key' => 'nullable|string',
           
            // API Google Maps
            'google_maps_api_key' => 'nullable|string',
            
        ]);

        // 2. Proses upload logo jika ada file baru yang diupload
        if ($request->hasFile('app_logo')) {
            // Hapus logo lama jika ada
            $oldLogoPath = Setting::where('key', 'app_logo')->value('value');
            if ($oldLogoPath) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            // Simpan logo baru dan dapatkan path-nya
            $path = $request->file('app_logo')->store('logos', 'public');
            // Simpan path ke dalam array yang akan disimpan ke database
            $validated['app_logo'] = $path;
        }

        // 3. Simpan semua data ke database menggunakan metode updateOrCreate
        foreach ($validated as $key => $value) {
            // Pastikan value tidak null sebelum menyimpan
            $valueToStore = $value ?? '';

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $valueToStore]
            );
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
