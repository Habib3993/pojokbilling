<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hapus user factory yang lama dan panggil seeder kita
        $this->call([
            LocationAndUserSeeder::class,
            // Anda bisa menambahkan seeder lain di sini di masa depan
        ]);
    }
}
