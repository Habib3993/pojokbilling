<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==========================================================
        // === KAMUS HAK AKSES (PERMISSIONS) SESUAI RENCANA BARU ===
        // ==========================================================

        // Permissions untuk Customers (Tidak ada perubahan)
        Permission::firstOrCreate(['name' => 'view customers']);
        Permission::firstOrCreate(['name' => 'create customers']);
        Permission::firstOrCreate(['name' => 'edit customers']);
        Permission::firstOrCreate(['name' => 'delete customers']);

        // Permissions untuk Transactions (Tidak ada perubahan)
        Permission::firstOrCreate(['name' => 'view transactions']);
        Permission::firstOrCreate(['name' => 'create transactions']);
        Permission::firstOrCreate(['name' => 'edit transactions']);
        Permission::firstOrCreate(['name' => 'delete transactions']);
        
        // Permissions untuk WhatsApp (Tidak ada perubahan)
        Permission::firstOrCreate(['name' => 'send whatsapp messages']);

        // PERBAIKAN: Izin untuk mengelola daftar Layer Kontrol di sidebar
        // Ini adalah data master yang hanya dikelola Superadmin
        Permission::firstOrCreate(['name' => 'view layer_groups']);
        Permission::firstOrCreate(['name' => 'create layer_groups']);
        Permission::firstOrCreate(['name' => 'edit layer_groups']);
        Permission::firstOrCreate(['name' => 'delete layer_groups']);

        // PERBAIKAN: Izin untuk mengelola data di Peta (Titik/Garis)
        // Ini adalah data transaksional yang dikelola Admin
        Permission::firstOrCreate(['name' => 'view map_data']);
        Permission::firstOrCreate(['name' => 'create map_data']);
        Permission::firstOrCreate(['name' => 'edit map_data']);
        Permission::firstOrCreate(['name' => 'delete map_data']);

        // DIHAPUS: Izin '...inventory_map' yang lama dihapus karena tumpang tindih dan membingungkan.


        // ===============================================================
        // === ATURAN MAIN (ASSIGN PERMISSION KE ROLE) SESUAI RENCANA ===
        // ===============================================================
        
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);

        // 1. Aturan untuk Viewer: Hanya bisa melihat semua data.
        $viewerRole->syncPermissions([
            'view customers',
            'view transactions',
            'view layer_groups', // Boleh lihat daftar Layer
            'view map_data',     // Boleh lihat titik/garis di peta
        ]);

        // 2. Aturan untuk Admin: Mengelola semua kecuali Layer Kontrol.
        $adminRole->syncPermissions([
            // Akses penuh Customer & Transaksi
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view transactions', 'create transactions', 'edit transactions', 'delete transactions',
            'send whatsapp messages',
            
            // Akses Peta
            'view layer_groups',    // HANYA boleh lihat daftar Layer
            'view map_data',        // Boleh lihat titik/garis di peta
            'create map_data',      // Boleh MEMBUAT titik/garis baru
            'edit map_data',        // Boleh MENGEDIT titik/garis
            'delete map_data',      // Boleh MENGHAPUS titik/garis
        ]);
        
        // 3. Superadmin tidak perlu diatur di sini, karena sudah diurus oleh AuthServiceProvider
        // yang memberinya akses penuh ke semua izin secara otomatis.

        $this->command->info('Hak akses sesuai rencana baru telah berhasil dibuat.');
    }
}
