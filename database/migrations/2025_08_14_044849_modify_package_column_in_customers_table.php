<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Hapus kolom 'paket' yang lama (berjenis teks) jika masih ada
            if (Schema::hasColumn('customers', 'paket')) {
            $table->dropColumn('paket');
            }

            // Tambah kolom 'package_id' baru yang terhubung ke tabel packages
            $table->foreignId('package_id')->nullable()->constrained('packages')->after('lokasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
