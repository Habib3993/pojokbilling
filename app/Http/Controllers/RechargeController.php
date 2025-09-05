<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use RouterOS\Client;
use RouterOS\Query;
use Barryvdh\DomPDF\Facade\Pdf;

class RechargeController extends Controller
{
    public function __construct()
    {
        // Hanya user dengan permission 'create transactions' yang bisa mengakses
        // semua fungsi di controller ini, KECUALI untuk download invoice.
        $this->middleware('can:create transactions')->except('downloadInvoice');

        // Mungkin user dengan permission 'view transactions' boleh download invoice.
        // Ini bisa ditambahkan jika perlu.
        // $this->middleware('can:view transactions')->only('downloadInvoice');
    }
    /**
     * Menampilkan form untuk isi ulang.
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $packages = Package::orderBy('name')->get();
        // Ambil customer yang dipilih dari URL, jika ada
        $selectedCustomer = null;
        if ($request->has('customer_id')) {
            $selectedCustomer = Customer::with('package')->find($request->customer_id);
        }
        return view('recharge.create', compact('customers', 'packages', 'selectedCustomer'));
    }

    /**
     * Mengambil detail pelanggan untuk form dinamis.
     */
    public function getCustomerDetails(Customer $customer)
    {
        $customer->load('package');

        if (!$customer->package) {
            return response()->json(['error' => 'Pelanggan ini tidak memiliki paket aktif.'], 404);
        }

        return response()->json([
            'package_id' => $customer->package_id,
            'customer_setor' => $customer->setor,
        ]);
    }

    /**
     * Menyimpan data isi ulang, perpanjang masa aktif, dan mencatat di buku kas.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|string',
            'amount' => 'required|integer|min:0', // Ini sekarang adalah total SETOR
            'amount' => 'required|integer|min:0',
            'payment_date' => 'required|date',
            'duration_months' => 'required|integer|min:1',
        ]);

        $customer = Customer::with('package.router')->findOrFail($validated['customer_id']);
        $package = Package::findOrFail($validated['package_id']);

        $expectedAmount = $customer->setor * $validated['duration_months'];
        if ($validated['amount'] != $expectedAmount) {
            return back()->withInput()->with('error', 'Jumlah pembayaran tidak sesuai dengan paket dan durasi yang dipilih.');
        }

        // 1. Simpan data pembayaran ke tabel 'payments'
        $payment = Payment::create([
            'customer_id' => $validated['customer_id'],
            'amount' => $validated['amount'],
            'duration_months' => $validated['duration_months'],
            'payment_date' => $validated['payment_date'],
            'description' => 'Pembayaran tagihan untuk ' . $validated['duration_months'] . ' bulan.',
            'sales_person' => auth()->user()->name,
        ]);

        // 2. Buat catatan di 'transactions' (Buku Kas)
        Transaction::create([
            'date' => $validated['payment_date'],
            'status' => 'MODAL',
            'note' => 'Pembayaran dari: ' . $customer->name,
            'debit' => $validated['amount'],
            'kredit' => 0,
            'optional_note' => 'Paket: ' . $package->name,
            'payment_id' => $payment->id,
        ]);

        // 3. Perpanjang masa aktif pelanggan
        $startDate = Carbon::parse($customer->active_until)->isPast()
                        ? Carbon::parse($validated['payment_date'])
                        : Carbon::parse($customer->active_until);

        $customer->update([
            'active_until' => $startDate->addMonths((int)$validated['duration_months'])
        ]);

        // 4. Re-aktivasi user di MikroTik
        $router = $customer->package->router ?? null;
        if ($router) {
            try {
                $client = new Client(['host' => $router->ip_address, 'user' => $router->username, 'pass' => $router->password]);
                $query = (new Query('/ppp/secret/print'))->where('name', $customer->name)->operations('=.id');
                $secretId = $client->query($query)->read()[0]['.id'] ?? null;

                if ($secretId) {
                    $enableQuery = (new Query('/ppp/secret/enable'))->equal('.id', $secretId);
                    $client->query($enableQuery)->read();
                }
            } catch (\Exception $e) {
                // Jika Mikrotik gagal, proses tetap lanjut tapi beri pesan peringatan
                return redirect()->route('transactions.index')
                    ->with('warning', "Pembayaran '{$customer->name}' berhasil, namun gagal re-aktivasi di MikroTik: " . $e->getMessage());
            }
        }

        // 5. Redirect Cerdas berdasarkan 'source'
        if ($request->input('source') === 'inactive_list') {
            // Jika datang dari halaman pelanggan tidak aktif, kembali ke sana
            return redirect()->route('customers.inactive')->with('success', 'Pembayaran berhasil, pelanggan telah diaktifkan.');
        }

        // Jika tidak, kembali ke halaman default (buku kas)
        return redirect()->route('transactions.index')->with('success', 'Pembayaran berhasil disimpan dan dicatat di Buku Kas.');

        // ===============================================================t di Buku Kas.');
    }

    /**
     * Menghasilkan dan menampilkan nota PDF.
     */
    public function downloadInvoice(Payment $payment)
    {
        $payment->load('customer.package');
        $packagePrice = $payment->customer->package->price; 
        $total = $packagePrice * $payment->duration_months;

        $subtotal = $total / 1.11;
        $ppn = $total - $subtotal;

        $history = Payment::where('customer_id', $payment->customer_id)
                            ->orderBy('payment_date', 'desc')
                            ->take(12)
                            ->get();
        $data = [
            'payment' => $payment,
            'subtotal' => $subtotal,
            'ppn' => $ppn,
            'total' => $total,
            'history' => $history,
        ];

        $pdf = Pdf::loadView('invoices.nota', $data);
        $fileName = 'invoice-' . $payment->customer->name . '-' . $payment->payment_date . '.pdf';

        return $pdf->stream($fileName);
    }
}