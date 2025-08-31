<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\Package;
use App\Models\RadCheck;
use App\Models\RadReply;

trait RadiusService
{
    /**
     * Membuat user baru di tabel RADIUS.
     */
    protected function createRadiusUser(Customer $customer, Package $package)
    {
        // 1. Simpan password di radcheck
        RadCheck::create([
            'username' => $customer->name, // <-- DIUBAH DARI USERNAME
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => '123456', // Password default Anda
        ]);

        // 2. Simpan info paket (profile) di radreply
        RadReply::create([
            'username' => $customer->name, // <-- DIUBAH DARI USERNAME
            'attribute' => 'Mikrotik-Group',
            'op' => '=',
            'value' => $package->name,
        ]);
    }

    /**
     * Mengupdate user di tabel RADIUS.
     */
    protected function updateRadiusUser(Customer $customer, Package $package)
    {
        $this->deleteRadiusUser($customer);
        $this->createRadiusUser($customer, $package);
    }

    /**
     * Menghapus user dari tabel RADIUS.
     */
    protected function deleteRadiusUser(Customer $customer)
    {
        RadCheck::where('username', $customer->name)->delete(); // <-- DIUBAH DARI USERNAME
        RadReply::where('username', $customer->name)->delete(); // <-- DIUBAH DARI USERNAME
    }
}