<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Untuk koneksi ke API Gateway

class WhatsappController extends Controller
{
    // Menampilkan form untuk kirim pesan pribadi
    /**
     * FUNGSI YANG HILANG ADA DI SINI
     * Menampilkan form untuk kirim pesan pribadi.
     */
    public function createPrivate()
    {
        $customers = Customer::orderBy('name')->get();
        return view('whatsapp.private', compact('customers'));
    }

    /**
     * Memproses pengiriman pesan pribadi.
     */
    public function sendPrivate(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'message' => 'required|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        try {
            // Logika untuk mengirim pesan via Fonnte
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_API_KEY')
            ])->post('https://api.fonnte.com/send', [
                'target'  => $customer->phone,
                'message' => $validated['message'],
            ]);

            if ($response->failed()) {
                Log::error('Fonnte API Error: ' . $response->body());
                return back()->with('error', 'Gagal mengirim pesan. Gateway merespons dengan error.');
            }

        } catch (\Exception $e) {
            Log::error('Fonnte Connection Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal terhubung ke WhatsApp Gateway: ' . $e->getMessage());
        }

        return back()->with('success', "Pesan berhasil dikirim ke {$customer->name}.");
    }

    /**
     * Menampilkan form untuk kirim pesan massal.
     */
    public function createBulk()
    {
        $odpGroups = Customer::whereNotNull('odp')->where('odp', '!=', '')->distinct()->pluck('odp');
        return view('whatsapp.bulk', compact('odpGroups'));
    }

    /**
     * Memproses pengiriman pesan massal.
     */
    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'odp_group' => 'required|string',
            'message' => 'required|string',
            'delay' => 'required|integer|min:1',
        ]);

        $customers = Customer::where('odp', $validated['odp_group'])->get();
        
        $results = [];
        foreach ($customers as $customer) {
            try {
                // Logika kirim pesan massal via Fonnte
                Http::withHeaders(['Authorization' => env('FONNTE_API_KEY')])
                    ->post('https://api.fonnte.com/send', [
                        'target' => $customer->phone,
                        'message' => $validated['message'],
                    ]);
                
                $results[] = ['name' => $customer->name, 'phone' => $customer->phone, 'status' => 'Sukses'];
            } catch (\Exception $e) {
                $results[] = ['name' => $customer->name, 'phone' => $customer->phone, 'status' => 'Gagal', 'reason' => $e->getMessage()];
            }
            // Beri jeda antar pesan
            sleep($validated['delay']);
        }

        return back()->with('results', $results)->with('success', 'Proses pengiriman massal selesai.');
    }
}
