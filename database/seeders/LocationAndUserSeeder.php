<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash; // <-- TAMBAHKAN INI

class LocationAndUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Cache permission dibersihkan.');

        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole      = Role::firstOrCreate(['name' => 'admin']);
        $viewerRole     = Role::firstOrCreate(['name' => 'viewer']);
        $this->command->info('Role superadmin, admin, dan viewer berhasil disiapkan.');

        // PERBAIKI DI SINI: Gunakan Hash::make()
        User::updateOrCreate(
            ['email' => 'pojok1@gmail.com'],
            [
                'name' => 'PojokNet',
                'password' => Hash::make('pojok1'), // <-- UBAH INI
                'location_id' => null,
            ]
        )->assignRole($superadminRole);
        $this->command->info('✅ Akun Superadmin (PojokNet) berhasil dibuat.');

        $adminsData = [
            ['location' => 'Pojoknet Tengah',  'name' => 'pojok2', 'email' => 'pojok2@gmail.com', 'password' => 'pojok2'],
            ['location' => 'Pojoknet Utara',   'name' => 'pojok3', 'email' => 'pojok3@gmail.com', 'password' => 'pojok3'],
            ['location' => 'Pojoknet Timur',   'name' => 'pojok4', 'email' => 'pojok4@gmail.com', 'password' => 'pojok4'],
            ['location' => 'Pojoknet Selatan', 'name' => 'pojok5', 'email' => 'pojok5@gmail.com', 'password' => 'pojok5'],
            ['location' => 'Pojoknet Barat',   'name' => 'pojok6', 'email' => 'pojok6@gmail.com', 'password' => 'pojok6'],
        ];

        foreach ($adminsData as $data) {
            $location = Location::firstOrCreate(['name' => $data['location']]);
            // PERBAIKI DI SINI: Gunakan Hash::make()
            User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make($data['password']), 'location_id' => $location->id] // <-- UBAH INI
            )->assignRole($adminRole);
            $this->command->info("✅ Admin '{$data['name']}' untuk '{$data['location']}' dibuat.");
        }

        $viewersData = [
            ['location' => 'Pojoknet Tengah',  'name' => 'Tamu Tengah',  'email' => 'tamu2@gmail.com', 'password' => 'tamu2'],
            ['location' => 'Pojoknet Utara',   'name' => 'Tamu Utara',   'email' => 'tamu3@gmail.com', 'password' => 'tamu3'],
            ['location' => 'Pojoknet Timur',   'name' => 'Tamu Timur',   'email' => 'tamu4@gmail.com', 'password' => 'tamu4'],
            ['location' => 'Pojoknet Selatan', 'name' => 'Tamu Selatan', 'email' => 'tamu5@gmail.com', 'password' => 'tamu5'],
            ['location' => 'Pojoknet Barat',   'name' => 'Tamu Barat',   'email' => 'tamu6@gmail.com', 'password' => 'tamu6'],
        ];

        foreach ($viewersData as $data) {
            $location = Location::where('name', $data['location'])->first();
            // PERBAIKI DI SINI: Gunakan Hash::make()
            User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make($data['password']), 'location_id' => $location->id] // <-- UBAH INI
            )->assignRole($viewerRole);
            $this->command->info("✅ Viewer '{$data['name']}' untuk '{$data['location']}' dibuat.");
        }
        
        $this->command->info('==================================');
        $this->command->info('Seeder Lokasi & User selesai dijalankan!');
    }
}