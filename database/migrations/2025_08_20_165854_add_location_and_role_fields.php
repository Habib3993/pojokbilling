<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom ke tabel 'users'
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email'); // 'superadmin' atau 'admin'
            $table->foreignId('location_id')->nullable()->constrained('locations')->after('role');
        });

        // Menambahkan kolom 'location_id' ke tabel-tabel data yang spesifik per lokasi
        $tables = [
            'customers', 
            'transactions',  // <-- DITAMBAHKAN
            'payments',      // <-- DITAMBAHKAN
            'map_points',    // <-- DITAMBAHKAN
            'map_polylines'  // <-- DITAMBAHKAN
        ]; 
        foreach ($tables as $tableName) {
            // Cek jika tabel ada sebelum mencoba mengubahnya
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('location_id')->nullable()->constrained('locations')->after('id');
                });
            }
        }
    }

    public function down(): void
    {
        // Logika untuk rollback jika diperlukan
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn(['role', 'location_id']);
        });

        $tables = [
            'customers', 
            'packages', 
            'olts', 
            'routers',
            'ip_pools',
            'vlans',
            'transactions',
            'payments',
            'map_points',
            'map_polylines'
        ];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'location_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['location_id']);
                    $table->dropColumn('location_id');
                });
            }
        }
    }
};
