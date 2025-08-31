<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-monthly-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // ...
    public function handle()
    {
        // Ambil semua pelanggan yang aktif
        $customers = Customer::where('status', 'active')->get(); // Anda mungkin perlu menambahkan kolom status di tabel customer

        foreach ($customers as $customer) {
            // Cek apakah pelanggan sudah punya invoice untuk bulan ini
            // ... (tambahkan logika ini agar tidak duplikat)

            Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => 'INV-' . time() . '-' . $customer->id, // Buat format yang lebih baik
                'amount' => $customer->package->price, // Ambil harga dari paket
                'billing_period_start' => now()->startOfMonth(),
                'due_date' => now()->startOfMonth()->addDays(10), // Jatuh tempo 10 hari
            ]);
        }

        $this->info('Monthly invoices have been generated successfully!');
    }
    // ...
}
