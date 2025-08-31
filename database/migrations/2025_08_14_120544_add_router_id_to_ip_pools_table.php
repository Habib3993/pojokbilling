<?php

// Typo diperbaiki di baris ini
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
        Schema::table('ip_pools', function (Blueprint $table) {
            // Tambahkan kolom baru 'router_id' yang terhubung ke tabel 'routers'
            // 'constrained' akan otomatis menghubungkannya ke kolom 'id' di tabel 'routers'
            // 'after('id')' menempatkan kolom ini setelah kolom 'id' agar rapi
            $table->foreignId('router_id')->constrained('routers')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_pools', function (Blueprint $table) {
            // Ini adalah kebalikan dari perintah di atas, untuk membatalkan migrasi
            $table->dropForeign(['router_id']);
            $table->dropColumn('router_id');
        });
    }
};
