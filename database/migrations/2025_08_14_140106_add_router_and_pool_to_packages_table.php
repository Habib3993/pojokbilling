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
        Schema::table('packages', function (Blueprint $table) {
            // Tambah kolom yang terhubung ke tabel routers
            $table->foreignId('router_id')->constrained('routers')->after('price');
            // Tambah kolom yang terhubung ke tabel ip_pools
            $table->foreignId('ip_pool_id')->constrained('ip_pools')->after('router_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            //
        });
    }
};
